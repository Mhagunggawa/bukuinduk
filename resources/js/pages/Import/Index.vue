<script setup lang="ts">
import { computed, onUnmounted, ref, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    FileSpreadsheet,
    Download,
    UploadCloud,
    FolderArchive,
    AlertTriangle,
    CheckCircle2,
    Info,
    FileText,
    Camera,
    Sparkles,
    UserX,
    UserCheck,
    Eye,
    FileWarning,
    ArrowRight,
    RefreshCw,
} from '@lucide/vue';
import BaseCard from '@/components/base/BaseCard.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSelect from '@/components/base/BaseSelect.vue';
import BaseBadge from '@/components/base/BaseBadge.vue';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseConfirmModal from '@/components/base/BaseConfirmModal.vue';
import BaseAlert from '@/components/base/BaseAlert.vue';
import { useConfirmModal } from '@/composables/useConfirmModal';
import {
    inspectZipPhotos,
    buildCompressedValidZip,
    revokeInspectionPreviews,
    formatFileSize,
    type SiswaIdentifier,
    type ZipInspectionResult,
    type MatchedZipPhoto,
    type UnmatchedZipPhoto,
    type InvalidTypeZipFile,
} from '@/lib/zipCompressor';

interface TahunPelajaran {
    id: number;
    tahun_pelajaran: string;
}

interface Jurusan {
    id: number;
    nama_konsentrasi: string;
    kode_singkat: string;
}

const props = withDefaults(
    defineProps<{
        tahunList: TahunPelajaran[];
        jurusanList: Jurusan[];
        siswaIdentifiers?: SiswaIdentifier[];
    }>(),
    {
        siswaIdentifiers: () => [],
    }
);

const page = usePage();
const flash = computed(() => (page.props as any).flash || {});
const importErrors = computed(() => flash.value.import_errors || []);

const activeTab = ref<'identitas' | 'nilai' | 'foto'>('identitas');

const ALLOWED_EXCEL_EXTENSIONS = ['xlsx', 'xls', 'csv'];

// Tab 1: Form Identitas
const identitasForm = useForm({
    file: null as File | null,
});
const identitasFileRef = ref<HTMLInputElement | null>(null);
const identitasFileName = ref<string>('');
const identitasFileError = ref<string>('');

const handleIdentitasFile = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    const ext = file.name.split('.').pop()?.toLowerCase() || '';
    if (!ALLOWED_EXCEL_EXTENSIONS.includes(ext)) {
        identitasFileError.value = `Tipe file keliru! Berkas "${file.name}" bertipe (.${ext.toUpperCase()}). Harap unggah berkas Excel (.XLSX, .XLS) atau .CSV.`;
        identitasForm.file = null;
        identitasFileName.value = '';
        input.value = '';
        return;
    }

    identitasFileError.value = '';
    identitasForm.file = file;
    identitasFileName.value = file.name;
};

const submitIdentitas = () => {
    if (!identitasForm.file) return;
    identitasForm.post('/import/identitas', {
        onSuccess: () => {
            identitasForm.reset();
            identitasFileName.value = '';
            identitasFileError.value = '';
            if (identitasFileRef.value) identitasFileRef.value.value = '';
        },
    });
};

// Tab 2: Form Nilai Semester
const nilaiForm = useForm({
    file: null as File | null,
    tahun_pelajaran_id: props.tahunList[0]?.id ? String(props.tahunList[0].id) : '',
    semester_ke: 1,
});
const nilaiFileRef = ref<HTMLInputElement | null>(null);
const nilaiFileName = ref<string>('');
const nilaiFileError = ref<string>('');

const handleNilaiFile = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    const ext = file.name.split('.').pop()?.toLowerCase() || '';
    if (!ALLOWED_EXCEL_EXTENSIONS.includes(ext)) {
        nilaiFileError.value = `Tipe file keliru! Berkas "${file.name}" bertipe (.${ext.toUpperCase()}). Harap unggah berkas Excel (.XLSX, .XLS) atau .CSV.`;
        nilaiForm.file = null;
        nilaiFileName.value = '';
        input.value = '';
        return;
    }

    nilaiFileError.value = '';
    nilaiForm.file = file;
    nilaiFileName.value = file.name;
};

const submitNilai = () => {
    if (!nilaiForm.file) return;
    nilaiForm.post('/import/nilai', {
        onSuccess: () => {
            nilaiForm.file = null;
            nilaiFileName.value = '';
            nilaiFileError.value = '';
            if (nilaiFileRef.value) nilaiFileRef.value.value = '';
        },
    });
};

// Tab 3: Form Upload ZIP Pasfoto + Pra-Validasi Tipe File, NIS/NISN, Deteksi Foto Lama & Kompresi Client-Side
const fotoForm = useForm({
    zip_file: null as File | null,
    overwrite_existing: true as boolean,
});
const fotoFileRef = ref<HTMLInputElement | null>(null);
const fotoFileName = ref<string>('');
const rawZipFile = ref<File | null>(null);
const isInspectingZip = ref(false);
const zipInspectionError = ref<string>('');
const zipInspection = ref<ZipInspectionResult | null>(null);

// Modal Daftar Konfirmasi Foto Lama, Tipe File Keliru & NIS / NISN Tidak Terdaftar
const isUnmatchedNisModalOpen = ref(false);
const existingPhotoModalItems = ref<MatchedZipPhoto[]>([]);
const unmatchedModalItems = ref<UnmatchedZipPhoto[]>([]);
const invalidTypeModalItems = ref<InvalidTypeZipFile[]>([]);

// Status Kompresi Foto Sebelum Upload
const isCompressingFoto = ref(false);
const compressionProgress = ref({ current: 0, total: 0 });
const compressionSummary = ref<{
    originalBytes: number;
    compressedBytes: number;
    savedPercent: number;
    photoCount: number;
} | null>(null);

onUnmounted(() => {
    revokeInspectionPreviews(zipInspection.value);
});

// Pantau jika server mengembalikan daftar NIS yang tidak terdaftar atau tipe file keliru via flash session
watch(
    () => [flash.value.unmatched_nis_list, flash.value.invalid_file_types],
    ([serverUnmatched, serverInvalidTypes]) => {
        let shouldOpen = false;

        if (Array.isArray(serverUnmatched) && serverUnmatched.length > 0) {
            unmatchedModalItems.value = serverUnmatched.map((item: any) => ({
                filename: item.file || '-',
                basename: item.file || '-',
                identifier: item.identifier || item.nisn || '-',
                uncompressedSize: 0,
                message: item.message || `NIS / NISN "${item.identifier || item.nisn}" tidak terdaftar.`,
            }));
            shouldOpen = true;
        }

        if (Array.isArray(serverInvalidTypes) && serverInvalidTypes.length > 0) {
            invalidTypeModalItems.value = serverInvalidTypes.map((item: any) => ({
                filename: item.file || '-',
                basename: item.file || '-',
                identifier: item.identifier || '-',
                ext: item.ext || 'Tipe Keliru',
                reason: item.message || `Tipe berkas "${item.file}" tidak didukung.`,
            }));
            shouldOpen = true;
        }

        if (shouldOpen) {
            isUnmatchedNisModalOpen.value = true;
        }
    },
    { immediate: true }
);

const handleFotoFile = async (e: Event) => {
    const input = e.target as HTMLInputElement;
    const selectedFile = input.files?.[0];
    if (!selectedFile) return;

    revokeInspectionPreviews(zipInspection.value);
    zipInspectionError.value = '';
    zipInspection.value = null;
    compressionSummary.value = null;
    existingPhotoModalItems.value = [];
    unmatchedModalItems.value = [];
    invalidTypeModalItems.value = [];

    // 1. Cek apakah file yang dipilih benar-benar memiliki ekstensi .zip
    const outerExt = selectedFile.name.split('.').pop()?.toLowerCase() || '';
    if (outerExt !== 'zip') {
        rawZipFile.value = null;
        fotoForm.zip_file = null;
        fotoFileName.value = '';
        input.value = '';

        const extDisplay = outerExt ? `.${outerExt.toUpperCase()}` : 'Tanpa Ekstensi';
        zipInspectionError.value = `Tipe file keliru! Berkas "${selectedFile.name}" bertipe ${extDisplay}. Pada fitur ini Anda wajib mengunggah berkas arsip .ZIP yang berisi kumpulan foto siswa (.JPG, .JPEG, .PNG, atau .WEBP).`;
        invalidTypeModalItems.value = [
            {
                filename: selectedFile.name,
                basename: selectedFile.name,
                identifier: '-',
                ext: extDisplay,
                reason: `Tipe berkas utama keliru (${extDisplay}). Sistem hanya menerima arsip kompresi .ZIP.`,
            },
        ];
        isUnmatchedNisModalOpen.value = true;
        return;
    }

    rawZipFile.value = selectedFile;
    fotoForm.zip_file = selectedFile;
    fotoFileName.value = selectedFile.name;

    isInspectingZip.value = true;
    try {
        const result = await inspectZipPhotos(selectedFile, props.siswaIdentifiers);
        zipInspection.value = result;
        existingPhotoModalItems.value = result.existingPhotos;
        unmatchedModalItems.value = result.unmatched;
        invalidTypeModalItems.value = result.invalidTypeFiles;

        if (result.totalFiles === 0) {
            zipInspectionError.value = 'Arsip ZIP kosong. Tidak ditemukan berkas apa pun di dalam arsip ZIP ini.';
        } else if (
            result.invalidTypeFiles.length > 0 ||
            result.unmatched.length > 0 ||
            result.existingPhotos.length > 0
        ) {
            if (result.invalidTypeFiles.length > 0) {
                zipInspectionError.value = `Ditemukan ${result.invalidTypeFiles.length} berkas dengan tipe file yang keliru/tidak sesuai di dalam ZIP! Hanya format foto .JPG, .JPEG, .PNG, dan .WEBP yang diperbolehkan.`;
            }
            // Langsung tampilkan modal konfirmasi foto lama, tipe file keliru, atau NIS tidak terdaftar
            isUnmatchedNisModalOpen.value = true;
        }
    } catch (err: any) {
        zipInspectionError.value = err?.message || 'Gagal membaca struktur berkas ZIP. Pastikan berkas benar-benar arsip .ZIP yang valid.';
        rawZipFile.value = null;
        fotoForm.zip_file = null;
    } finally {
        isInspectingZip.value = false;
    }
};

const executeCompressAndUploadFoto = async (mode: 'overwrite_all' | 'skip_existing' = 'overwrite_all') => {
    if (!rawZipFile.value) return;

    isUnmatchedNisModalOpen.value = false;
    const overwrite = mode === 'overwrite_all';
    fotoForm.overwrite_existing = overwrite;

    try {
        // Filter foto yang akan diproses berdasarkan pilihan pengguna ('overwrite_all' vs 'skip_existing')
        const targetPhotos = zipInspection.value
            ? overwrite
                ? zipInspection.value.matched
                : zipInspection.value.newPhotos
            : [];

        if (targetPhotos.length > 0) {
            isCompressingFoto.value = true;
            compressionProgress.value = {
                current: 0,
                total: targetPhotos.length,
            };

            const { zipFile, originalBytes, compressedBytes } = await buildCompressedValidZip(
                rawZipFile.value,
                targetPhotos,
                (curr, tot) => {
                    compressionProgress.value = { current: curr, total: tot };
                }
            );

            const savedPercent =
                originalBytes > 0
                    ? Math.max(0, Math.round(((originalBytes - compressedBytes) / originalBytes) * 100))
                    : 0;

            compressionSummary.value = {
                originalBytes,
                compressedBytes,
                savedPercent,
                photoCount: targetPhotos.length,
            };

            fotoForm.zip_file = zipFile;
        } else {
            fotoForm.zip_file = rawZipFile.value;
        }
    } catch {
        // Fallback ke file ZIP asli (Server tetap memvalidasi tipe file, NIS/NISN, overwrite_existing & mengompres via PHP GD)
        fotoForm.zip_file = rawZipFile.value;
    } finally {
        isCompressingFoto.value = false;
    }

    fotoForm.post('/import/zip-foto', {
        onSuccess: () => {
            revokeInspectionPreviews(zipInspection.value);
            fotoForm.reset();
            rawZipFile.value = null;
            fotoFileName.value = '';
            zipInspection.value = null;
            zipInspectionError.value = '';
            existingPhotoModalItems.value = [];
            if (fotoFileRef.value) fotoFileRef.value.value = '';
        },
    });
};

const romanSemesters = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

const {
    isOpen: isConfirmOpen,
    isLoading: isConfirmLoading,
    options: confirmOptions,
    openConfirm,
    closeConfirm,
    handleConfirm,
} = useConfirmModal();

const promptSubmitIdentitas = () => {
    if (!identitasForm.file) return;
    openConfirm({
        title: 'Konfirmasi Import Data Identitas Siswa',
        message: 'Apakah Anda yakin ingin memproses dan mengimpor berkas ini? Sistem akan menambahkan data siswa baru dan memperbarui data lama jika NISN telah terdaftar.',
        itemInfo: `Berkas Excel: ${identitasFileName.value}`,
        confirmText: 'Ya, Mulai Import',
        variant: 'primary',
        onConfirm: () => {
            submitIdentitas();
        },
    });
};

const promptSubmitNilai = () => {
    if (!nilaiForm.file) return;
    const tahun = props.tahunList.find((t) => String(t.id) === String(nilaiForm.tahun_pelajaran_id))?.tahun_pelajaran || '-';
    openConfirm({
        title: 'Konfirmasi Import Nilai Akademik Semester',
        message: 'Apakah Anda yakin ingin memproses dan menyimpan seluruh nilai akademik dari berkas ini?',
        itemInfo: `Berkas Excel: ${nilaiFileName.value}`,
        subInfo: `Tahun Pelajaran: ${tahun} • Semester: ${romanSemesters[nilaiForm.semester_ke - 1]} (${nilaiForm.semester_ke})`,
        confirmText: 'Ya, Mulai Import Nilai',
        variant: 'primary',
        onConfirm: () => {
            submitNilai();
        },
    });
};

const promptSubmitFoto = () => {
    if (!rawZipFile.value) return;

    // Jika ditemukan foto siswa yang sudah punya foto sebelumnya, tipe file yang keliru, ATAU NIS/NISN yang tidak terdaftar, tampilkan Modal Konfirmasi terlebih dahulu
    if (
        zipInspection.value &&
        (zipInspection.value.existingPhotos.length > 0 ||
            zipInspection.value.invalidTypeFiles.length > 0 ||
            zipInspection.value.unmatched.length > 0)
    ) {
        existingPhotoModalItems.value = zipInspection.value.existingPhotos;
        unmatchedModalItems.value = zipInspection.value.unmatched;
        invalidTypeModalItems.value = zipInspection.value.invalidTypeFiles;
        isUnmatchedNisModalOpen.value = true;
        return;
    }

    const validCount = zipInspection.value?.matched.length ?? 0;
    const origSize = zipInspection.value ? formatFileSize(zipInspection.value.totalValidOriginalBytes) : '';

    openConfirm({
        title: 'Konfirmasi Kompresi & Ekstrak Pasfoto Siswa Baru',
        message: 'Seluruh tipe berkas foto dan NIS/NISN pada arsip ZIP telah terverifikasi sesuai (belum ada foto sebelumnya). Sistem akan mengompres ukuran foto (proporsi pasfoto 3x4 maks 450x600px) terlebih dahulu sebelum mengunggah ke server.',
        itemInfo: `Arsip ZIP: ${fotoFileName.value}`,
        subInfo: validCount > 0 ? `${validCount} Pasfoto Siswa Terdaftar (${origSize})` : undefined,
        confirmText: 'Ya, Kompres & Upload Foto',
        variant: 'primary',
        onConfirm: () => {
            executeCompressAndUploadFoto('overwrite_all');
        },
    });
};

const modalTitle = computed(() => {
    const hasInvalid = invalidTypeModalItems.value.length > 0;
    const hasUnmatched = unmatchedModalItems.value.length > 0;
    const hasExisting = existingPhotoModalItems.value.length > 0;

    if (hasExisting && !hasInvalid && !hasUnmatched) {
        return 'Konfirmasi Penggantian Pasfoto Siswa yang Sudah Ada';
    }
    if (hasExisting && (hasInvalid || hasUnmatched)) {
        return 'Konfirmasi Penggantian Foto Lama & Laporan Pemeriksaan ZIP';
    }
    if (hasInvalid && hasUnmatched) {
        return 'Peringatan: Ditemukan Tipe File Keliru & NIS Tidak Terdaftar';
    }
    if (hasInvalid) {
        return 'Peringatan: Ditemukan Tipe File yang Keliru / Tidak Sesuai';
    }
    return 'Peringatan: Ditemukan NIS / NISN Tidak Terdaftar';
});
</script>

<template>
    <Head title="Import Data Massal - SI KARANJI" />

    <div class="space-y-6 max-w-5xl mx-auto">
        <!-- Header Section -->
        <div class="bg-white p-5 rounded-[10px] border border-[#E2E8F0] shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-[#0F3A5F] tracking-tight">
                    Import Massal Data Siswa
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Unggah ratusan data identitas, nilai akademik semester, dan arsip pasfoto secara terstruktur
                </p>
            </div>

            <div class="flex items-center gap-2">
                <BaseBadge variant="success" size="md" dot>
                    Batch Upsert & Auto-Compress Aktif
                </BaseBadge>
            </div>
        </div>

        <!-- 3 Tab Navigasi -->
        <div class="bg-white p-1 rounded-[10px] border border-[#E2E8F0] shadow-xs flex flex-wrap sm:flex-nowrap gap-1">
            <button
                type="button"
                :class="[
                    'flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-[8px] text-xs font-semibold transition-colors cursor-pointer',
                    activeTab === 'identitas'
                        ? 'bg-[#EAF4FC] text-[#2B7CC2] border border-[#BFE3FA]'
                        : 'text-[#64748B] hover:text-[#0F3A5F] hover:bg-[#F8FAFC]',
                ]"
                @click="activeTab = 'identitas'"
            >
                <FileSpreadsheet class="w-4 h-4" />
                <span>1. Identitas Siswa</span>
            </button>

            <button
                type="button"
                :class="[
                    'flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-[8px] text-xs font-semibold transition-colors cursor-pointer',
                    activeTab === 'nilai'
                        ? 'bg-[#EAF4FC] text-[#2B7CC2] border border-[#BFE3FA]'
                        : 'text-[#64748B] hover:text-[#0F3A5F] hover:bg-[#F8FAFC]',
                ]"
                @click="activeTab = 'nilai'"
            >
                <FileText class="w-4 h-4" />
                <span>2. Nilai & Absensi</span>
            </button>

            <button
                type="button"
                :class="[
                    'flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-[8px] text-xs font-semibold transition-colors cursor-pointer',
                    activeTab === 'foto'
                        ? 'bg-[#EAF4FC] text-[#2B7CC2] border border-[#BFE3FA]'
                        : 'text-[#64748B] hover:text-[#0F3A5F] hover:bg-[#F8FAFC]',
                ]"
                @click="activeTab = 'foto'"
            >
                <FolderArchive class="w-4 h-4" />
                <span>3. Pasfoto ZIP</span>
            </button>
        </div>

        <!-- ================= TAB 1: IDENTITAS SISWA ================= -->
        <div v-if="activeTab === 'identitas'" class="space-y-5">
            <!-- Langkah 1: Unduh Format -->
            <BaseCard padding="md">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1 max-w-xl">
                        <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2B7CC2] bg-[#EAF4FC] px-2.5 py-0.5 rounded-[6px] border border-[#BFE3FA]">
                            <Info class="w-3.5 h-3.5" />
                            <span>Langkah 1: Format Baku Excel</span>
                        </div>
                        <h3 class="text-sm font-bold text-[#0F3A5F] mt-1">
                            Template Identitas & Biodata Siswa (.xlsx)
                        </h3>
                        <p class="text-xs text-[#64748B] leading-relaxed">
                            Unduh template resmi yang berisi struktur kolom standar: NIS, NISN (kunci identifikasi utama), Nama Siswa, Kode Jurusan, Tempat/Tgl Lahir, Data Wali, hingga STTB.
                        </p>
                    </div>

                    <BaseButton
                        href="/import/template-identitas"
                        external
                        download="template_identitas_siswa.xlsx"
                        variant="secondary"
                        size="md"
                        class="shrink-0"
                    >
                        <Download class="w-4 h-4 text-[#2B7CC2]" />
                        <span>Unduh Template Identitas</span>
                    </BaseButton>
                </div>
            </BaseCard>

            <!-- Langkah 2: Unggah File -->
            <BaseCard title="Langkah 2: Unggah File Excel Identitas">
                <form @submit.prevent="promptSubmitIdentitas" class="space-y-4">
                    <div
                        class="border-2 border-dashed border-[#BFE3FA] hover:border-[#2B7CC2] bg-[#F8FAFC] hover:bg-[#EAF4FC]/30 rounded-[10px] p-8 text-center transition-colors cursor-pointer flex flex-col items-center justify-center"
                        @click="identitasFileRef?.click()"
                    >
                        <input
                            ref="identitasFileRef"
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            class="hidden"
                            @change="handleIdentitasFile"
                        />
                        <div class="w-12 h-12 rounded-[10px] bg-[#EAF4FC] text-[#2B7CC2] flex items-center justify-center mb-2.5 border border-[#BFE3FA]">
                            <UploadCloud class="w-6 h-6" />
                        </div>

                        <div v-if="identitasFileName" class="space-y-1">
                            <span class="text-xs font-semibold text-[#2B7CC2] bg-[#EAF4FC] border border-[#BFE3FA] px-3 py-1 rounded-[6px]">
                                File Terpilih: {{ identitasFileName }}
                            </span>
                            <p class="text-[11px] text-[#64748B]">Klik untuk mengganti berkas</p>
                        </div>
                        <div v-else class="space-y-1">
                            <p class="text-xs sm:text-sm font-semibold text-[#0F3A5F]">
                                Tarik & letakkan file Excel di sini, atau <span class="text-[#2B7CC2] underline">Pilih Berkas</span>
                            </p>
                            <p class="text-[11px] text-[#64748B]">
                                Format .XLSX, .XLS, atau .CSV (Maksimal 20MB)
                            </p>
                        </div>
                    </div>

                    <div v-if="identitasFileError" class="p-3.5 rounded-[8px] bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center gap-2">
                        <FileWarning class="w-4 h-4 text-rose-600 shrink-0" />
                        <span>{{ identitasFileError }}</span>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs text-[#64748B]">
                            * Sistem otomatis memperbarui (upsert) jika NISN sudah terdaftar.
                        </span>

                        <BaseButton
                            type="submit"
                            variant="primary"
                            size="md"
                            :disabled="!identitasForm.file || identitasForm.processing"
                            :loading="identitasForm.processing"
                        >
                            <UploadCloud class="w-4 h-4" />
                            <span>Mulai Import Identitas</span>
                        </BaseButton>
                    </div>
                </form>
            </BaseCard>
        </div>

        <!-- ================= TAB 2: NILAI SEMESTER ================= -->
        <div v-if="activeTab === 'nilai'" class="space-y-5">
            <BaseCard padding="md">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1 max-w-xl">
                        <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2B7CC2] bg-[#EAF4FC] px-2.5 py-0.5 rounded-[6px] border border-[#BFE3FA]">
                            <Info class="w-3.5 h-3.5" />
                            <span>Langkah 1: Format Baku Nilai</span>
                        </div>
                        <h3 class="text-sm font-bold text-[#0F3A5F] mt-1">
                            Template Nilai Akademik & Absensi (.xlsx)
                        </h3>
                        <p class="text-xs text-[#64748B] leading-relaxed">
                            Format Excel yang mencakup daftar nilai mata pelajaran umum dan kejuruan, nilai ekstrakurikuler, PKL, ranking, dan rekapitulasi kehadiran per Tahun Pelajaran & Semester.
                        </p>
                    </div>

                    <BaseButton
                        href="/import/template-nilai"
                        external
                        download="template_nilai_semester.xlsx"
                        variant="secondary"
                        size="md"
                        class="shrink-0"
                    >
                        <Download class="w-4 h-4 text-[#2B7CC2]" />
                        <span>Unduh Template Nilai</span>
                    </BaseButton>
                </div>
            </BaseCard>

            <BaseCard title="Langkah 2: Pilih Tahun Pelajaran, Target Semester & Unggah Berkas">
                <form @submit.prevent="promptSubmitNilai" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 bg-[#F8FAFC] rounded-[8px] border border-[#E2E8F0]">
                        <BaseSelect
                            v-model="nilaiForm.tahun_pelajaran_id"
                            label="Tahun Pelajaran"
                            required
                        >
                            <option value="" disabled>-- Pilih Tahun Pelajaran --</option>
                            <option v-for="t in tahunList" :key="t.id" :value="t.id">
                                {{ t.tahun_pelajaran }}
                            </option>
                        </BaseSelect>

                        <BaseSelect
                            v-model.number="nilaiForm.semester_ke"
                            label="Target Semester (I - X)"
                            required
                        >
                            <option v-for="(rom, idx) in romanSemesters" :key="idx" :value="idx + 1">
                                Semester {{ rom }} (Semester {{ idx + 1 }})
                            </option>
                        </BaseSelect>
                    </div>

                    <div
                        class="border-2 border-dashed border-[#BFE3FA] hover:border-[#2B7CC2] bg-[#F8FAFC] hover:bg-[#EAF4FC]/30 rounded-[10px] p-8 text-center transition-colors cursor-pointer flex flex-col items-center justify-center"
                        @click="nilaiFileRef?.click()"
                    >
                        <input
                            ref="nilaiFileRef"
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            class="hidden"
                            @change="handleNilaiFile"
                        />
                        <div class="w-12 h-12 rounded-[10px] bg-[#EAF4FC] text-[#2B7CC2] flex items-center justify-center mb-2.5 border border-[#BFE3FA]">
                            <FileSpreadsheet class="w-6 h-6" />
                        </div>

                        <div v-if="nilaiFileName" class="space-y-1">
                            <span class="text-xs font-semibold text-[#2B7CC2] bg-[#EAF4FC] border border-[#BFE3FA] px-3 py-1 rounded-[6px]">
                                File Terpilih: {{ nilaiFileName }}
                            </span>
                            <p class="text-[11px] text-[#64748B]">Klik untuk mengganti berkas</p>
                        </div>
                        <div v-else class="space-y-1">
                            <p class="text-xs sm:text-sm font-semibold text-[#0F3A5F]">
                                Tarik & letakkan file Nilai Excel di sini, atau <span class="text-[#2B7CC2] underline">Pilih Berkas</span>
                            </p>
                            <p class="text-[11px] text-[#64748B]">
                                Rata-rata dan jumlah nilai akan dihitung otomatis oleh sistem
                            </p>
                        </div>
                    </div>

                    <div v-if="nilaiFileError" class="p-3.5 rounded-[8px] bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center gap-2">
                        <FileWarning class="w-4 h-4 text-rose-600 shrink-0" />
                        <span>{{ nilaiFileError }}</span>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs text-[#64748B]">
                            * Nilai disimpan terpisah untuk Tahun Pelajaran & Semester yang dipilih.
                        </span>

                        <BaseButton
                            type="submit"
                            variant="primary"
                            size="md"
                            :disabled="!nilaiForm.file || nilaiForm.processing"
                            :loading="nilaiForm.processing"
                        >
                            <UploadCloud class="w-4 h-4" />
                            <span>Mulai Import Nilai</span>
                        </BaseButton>
                    </div>
                </form>
            </BaseCard>
        </div>

        <!-- ================= TAB 3: UPLOAD PASFOTO ZIP ================= -->
        <div v-if="activeTab === 'foto'" class="space-y-5">
            <BaseCard title="Panduan Berkas ZIP Pasfoto & Konfirmasi Foto Lama">
                <div class="space-y-3 text-xs text-[#64748B]">
                    <p>
                        Masukkan pasfoto seluruh siswa (format <strong>JPG, JPEG, PNG, atau WEBP</strong>) ke dalam satu arsip kompresi <strong>.ZIP</strong>.
                        Sistem akan <strong>memeriksa kesesuaian tipe file, memverifikasi NIS / NISN, mendeteksi siswa yang sudah memiliki pasfoto sebelumnya</strong>, serta <strong>mengompres ukuran foto otomatis</strong>.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3 rounded-[8px] bg-[#EAF4FC] border border-[#BFE3FA] text-[#0F3A5F] space-y-1">
                            <p class="font-bold flex items-center gap-1.5">
                                <UserCheck class="w-3.5 h-3.5 text-[#2B7CC2]" />
                                Ketentuan Penamaan & Tipe File Foto:
                            </p>
                            <p class="font-mono text-[11px]">
                                [NIS].jpg / .png / .webp &nbsp;atau&nbsp; [NISN].jpg / .png / .webp
                            </p>
                        </div>
                        <div class="p-3 rounded-[8px] bg-emerald-50/70 border border-emerald-200 text-emerald-950 space-y-1">
                            <p class="font-bold flex items-center gap-1.5">
                                <Sparkles class="w-3.5 h-3.5 text-emerald-600" />
                                Filtering Foto Lama & Kompresi Cerdas:
                            </p>
                            <p class="text-[11px] text-emerald-800">
                                Jika siswa sudah memiliki foto sebelumnya, sistem menampilkan pratinjau perbandingan (Foto Lama vs Foto Baru) untuk dikonfirmasi apakah ingin diganti atau dilewati.
                            </p>
                        </div>
                    </div>
                </div>
            </BaseCard>

            <BaseCard title="Unggah Berkas ZIP Pasfoto">
                <form @submit.prevent="promptSubmitFoto" class="space-y-4">
                    <div
                        class="border-2 border-dashed border-[#BFE3FA] hover:border-[#2B7CC2] bg-[#F8FAFC] hover:bg-[#EAF4FC]/30 rounded-[10px] p-8 text-center transition-colors cursor-pointer flex flex-col items-center justify-center"
                        @click="fotoFileRef?.click()"
                    >
                        <input
                            ref="fotoFileRef"
                            type="file"
                            accept=".zip"
                            class="hidden"
                            @change="handleFotoFile"
                        />
                        <div class="w-12 h-12 rounded-[10px] bg-[#EAF4FC] text-[#2B7CC2] flex items-center justify-center mb-2.5 border border-[#BFE3FA]">
                            <FolderArchive class="w-6 h-6" />
                        </div>

                        <div v-if="fotoFileName" class="space-y-1">
                            <span class="text-xs font-semibold text-[#2B7CC2] bg-[#EAF4FC] border border-[#BFE3FA] px-3 py-1 rounded-[6px]">
                                Berkas ZIP: {{ fotoFileName }}
                            </span>
                            <p class="text-[11px] text-[#64748B]">Klik untuk mengganti arsip</p>
                        </div>
                        <div v-else class="space-y-1">
                            <p class="text-xs sm:text-sm font-semibold text-[#0F3A5F]">
                                Tarik & letakkan file .ZIP di sini, atau <span class="text-[#2B7CC2] underline">Pilih Berkas</span>
                            </p>
                            <p class="text-[11px] text-[#64748B]">
                                Mendukung arsip ZIP hingga 50MB (Tipe file, NIS/NISN & foto lama diperiksa otomatis)
                            </p>
                        </div>
                    </div>

                    <!-- Hasil Pra-Validasi Isi File ZIP -->
                    <div v-if="isInspectingZip" class="p-3.5 rounded-[8px] bg-[#F8FAFC] border border-[#E2E8F0] text-xs text-[#64748B] flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#2B7CC2] animate-ping"></span>
                        <span>Memeriksa tipe berkas foto, kecocokan NIS/NISN, dan riwayat foto siswa...</span>
                    </div>

                    <!-- Pesan Peringatan Jika Ada Tipe File yang Keliru -->
                    <div
                        v-if="zipInspectionError"
                        class="p-3.5 rounded-[8px] bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-start justify-between gap-3"
                    >
                        <div class="flex items-start gap-2">
                            <FileWarning class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" />
                            <div class="space-y-1">
                                <span class="font-bold block text-rose-900">Peringatan Ketidaksesuaian Tipe File:</span>
                                <span>{{ zipInspectionError }}</span>
                            </div>
                        </div>
                        <button
                            v-if="invalidTypeModalItems.length > 0"
                            type="button"
                            class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] bg-rose-600 hover:bg-rose-700 text-white font-semibold text-[11px] transition-colors cursor-pointer"
                            @click="isUnmatchedNisModalOpen = true"
                        >
                            <Eye class="w-3 h-3" />
                            <span>Detail File Keliru</span>
                        </button>
                    </div>

                    <div v-if="zipInspection && !isInspectingZip" class="p-4 rounded-[10px] bg-[#F8FAFC] border border-[#E2E8F0] space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="text-xs font-bold text-[#0F3A5F]">
                                Hasil Pemeriksaan Isi Berkas ZIP ({{ zipInspection.totalFiles }} Berkas Diperiksa)
                            </div>
                            <button
                                v-if="
                                    zipInspection.existingPhotos.length > 0 ||
                                    zipInspection.unmatched.length > 0 ||
                                    zipInspection.invalidTypeFiles.length > 0
                                "
                                type="button"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2B7CC2] bg-[#EAF4FC] hover:bg-[#BFE3FA]/50 border border-[#BFE3FA] px-2.5 py-1 rounded-[6px] transition-colors cursor-pointer"
                                @click="isUnmatchedNisModalOpen = true"
                            >
                                <Eye class="w-3.5 h-3.5" />
                                <span>
                                    Buka Modal Konfirmasi ({{ zipInspection.existingPhotos.length }} Foto Lama • {{ zipInspection.invalidTypeFiles.length }} Tipe Keliru • {{ zipInspection.unmatched.length }} NIS Tidak Ada)
                                </span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                            <div class="p-3 rounded-[8px] bg-white border border-emerald-200 flex items-center justify-between">
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Foto Siswa Baru</span>
                                    <span class="text-base font-black text-emerald-700">{{ zipInspection.newPhotos.length }} Foto</span>
                                </div>
                                <CheckCircle2 class="w-5 h-5 text-emerald-600" />
                            </div>

                            <div
                                class="p-3 rounded-[8px] bg-white border flex items-center justify-between"
                                :class="zipInspection.existingPhotos.length > 0 ? 'border-[#2B7CC2] bg-[#EAF4FC]/40' : 'border-[#E2E8F0]'"
                            >
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Sudah Ada Foto Lama</span>
                                    <span
                                        class="text-base font-black"
                                        :class="zipInspection.existingPhotos.length > 0 ? 'text-[#2B7CC2]' : 'text-slate-500'"
                                    >
                                        {{ zipInspection.existingPhotos.length }} Siswa
                                    </span>
                                </div>
                                <RefreshCw class="w-5 h-5" :class="zipInspection.existingPhotos.length > 0 ? 'text-[#2B7CC2]' : 'text-slate-300'" />
                            </div>

                            <div
                                class="p-3 rounded-[8px] bg-white border flex items-center justify-between"
                                :class="zipInspection.invalidTypeFiles.length > 0 ? 'border-rose-300 bg-rose-50/40' : 'border-[#E2E8F0]'"
                            >
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">Tipe File Keliru</span>
                                    <span
                                        class="text-base font-black"
                                        :class="zipInspection.invalidTypeFiles.length > 0 ? 'text-rose-600' : 'text-slate-500'"
                                    >
                                        {{ zipInspection.invalidTypeFiles.length }} Berkas
                                    </span>
                                </div>
                                <FileWarning class="w-5 h-5" :class="zipInspection.invalidTypeFiles.length > 0 ? 'text-rose-600' : 'text-slate-300'" />
                            </div>

                            <div
                                class="p-3 rounded-[8px] bg-white border flex items-center justify-between"
                                :class="zipInspection.unmatched.length > 0 ? 'border-amber-300 bg-amber-50/30' : 'border-[#E2E8F0]'"
                            >
                                <div>
                                    <span class="text-[#64748B] block text-[11px]">NIS / NISN Tidak Ada</span>
                                    <span
                                        class="text-base font-black"
                                        :class="zipInspection.unmatched.length > 0 ? 'text-amber-600' : 'text-slate-500'"
                                    >
                                        {{ zipInspection.unmatched.length }} Foto
                                    </span>
                                </div>
                                <UserX class="w-5 h-5" :class="zipInspection.unmatched.length > 0 ? 'text-amber-500' : 'text-slate-300'" />
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar Saat Mengompres Foto Sebelum Upload -->
                    <div v-if="isCompressingFoto" class="p-4 rounded-[10px] bg-[#EAF4FC] border border-[#BFE3FA] space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-[#0F3A5F]">
                            <span class="flex items-center gap-1.5">
                                <Sparkles class="w-4 h-4 text-[#2B7CC2] animate-spin" />
                                Mengompres foto pasfoto 3x4 sebelum upload ke server...
                            </span>
                            <span>{{ compressionProgress.current }} / {{ compressionProgress.total }} Foto</span>
                        </div>
                        <div class="w-full h-2 bg-white rounded-full overflow-hidden border border-[#BFE3FA]">
                            <div
                                class="h-full bg-[#2B7CC2] transition-all duration-150"
                                :style="{
                                    width: `${compressionProgress.total > 0 ? Math.round((compressionProgress.current / compressionProgress.total) * 100) : 0}%`,
                                }"
                            ></div>
                        </div>
                    </div>

                    <!-- Ringkasan Hasil Kompresi Terakhir -->
                    <div v-if="compressionSummary && !isCompressingFoto" class="p-3.5 rounded-[8px] bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                            <span>
                                <strong>{{ compressionSummary.photoCount }} foto valid</strong> berhasil dikompres dari
                                <strong>{{ formatFileSize(compressionSummary.originalBytes) }}</strong> menjadi
                                <strong>{{ formatFileSize(compressionSummary.compressedBytes) }}</strong>
                                (Hemat {{ compressionSummary.savedPercent }}% beban server).
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs text-[#64748B]">
                            * Berkas dengan tipe file keliru atau NIS/NISN tidak terdaftar akan ditolak & tidak disimpan ke server.
                        </span>

                        <BaseButton
                            type="submit"
                            variant="primary"
                            size="md"
                            :disabled="
                                !rawZipFile ||
                                fotoForm.processing ||
                                isCompressingFoto ||
                                isInspectingZip ||
                                (zipInspection !== null && zipInspection.totalFiles === 0)
                            "
                            :loading="fotoForm.processing || isCompressingFoto"
                        >
                            <Camera class="w-4 h-4" />
                            <span>Mulai Ekstrak Pasfoto</span>
                        </BaseButton>
                    </div>
                </form>
            </BaseCard>
        </div>

        <!-- Error Log Box (Untuk Import Excel Identitas / Nilai) -->
        <BaseAlert
            v-if="importErrors.length > 0 && activeTab !== 'foto'"
            type="error"
            title="Daftar Baris yang Gagal Diimpor"
        >
            <ul class="list-disc list-inside text-xs space-y-1 font-mono mt-2">
                <li v-for="(err, idx) in importErrors" :key="idx">
                    {{ typeof err === 'object' ? `${err.nisn || err.file || '-'}: ${err.message}` : err }}
                </li>
            </ul>
        </BaseAlert>

        <!-- ================= MODAL KONFIRMASI PENGGANTIAN FOTO LAMA, TIPE FILE KELIRU & NIS TIDAK TERDAFTAR ================= -->
        <BaseModal
            :open="isUnmatchedNisModalOpen"
            :title="modalTitle"
            description="Hasil pemeriksaan foto yang sudah ada sebelumnya, validitas tipe berkas, dan kecocokan NIS / NISN"
            size="3xl"
            @close="isUnmatchedNisModalOpen = false"
        >
            <div class="space-y-5 text-xs">
                <!-- BAGIAN 1: KONFIRMASI SISWA YANG SUDAH MEMILIKI PASFOTO SEBELUMNYA (FOTO LAMA VS FOTO BARU) -->
                <div v-if="existingPhotoModalItems.length > 0" class="space-y-3">
                    <div class="p-3.5 rounded-[10px] bg-[#EAF4FC] border border-[#BFE3FA] text-[#0F3A5F] flex items-start gap-3">
                        <RefreshCw class="w-5 h-5 text-[#2B7CC2] shrink-0 mt-0.5" />
                        <div class="space-y-1">
                            <p class="font-bold text-[#0F3A5F]">
                                Ditemukan {{ existingPhotoModalItems.length }} siswa yang sudah memiliki pasfoto sebelumnya!
                            </p>
                            <p class="text-[#0F3A5F]/85 leading-relaxed">
                                Silakan periksa perbandingan <strong>Foto Lama</strong> dan <strong>Foto Baru</strong> di bawah ini, lalu konfirmasikan melalui tombol di bagian bawah apakah foto lama ingin <strong>digantikan seluruhnya</strong> atau <strong>dilewati</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="border border-[#BFE3FA] rounded-[8px] overflow-hidden max-h-72 overflow-y-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-[#0F3A5F] text-white text-[11px] uppercase font-bold sticky top-0 z-10">
                                <tr>
                                    <th class="px-3 py-2.5 w-10 text-center">No</th>
                                    <th class="px-3 py-2.5">Identitas Siswa</th>
                                    <th class="px-3 py-2.5 text-center w-56">Perbandingan (Foto Lama → Baru)</th>
                                    <th class="px-3 py-2.5 w-32 text-center">Berkas ZIP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0] bg-white">
                                <tr
                                    v-for="(item, idx) in existingPhotoModalItems"
                                    :key="`ex-${item.siswa.id}-${idx}`"
                                    class="hover:bg-[#F8FAFC]"
                                >
                                    <td class="px-3 py-2.5 text-center text-[#64748B] font-mono">
                                        {{ idx + 1 }}
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <div class="font-bold text-[#0F3A5F] uppercase">
                                            {{ item.siswa.nama_lengkap }}
                                        </div>
                                        <div class="text-[11px] text-[#64748B] font-mono mt-0.5">
                                            NIS: <strong class="text-[#0F3A5F]">{{ item.siswa.nis }}</strong> • NISN: <strong class="text-[#0F3A5F]">{{ item.siswa.nisn }}</strong>
                                        </div>
                                        <div v-if="item.siswa.kelas_saat_ini" class="text-[10.5px] text-[#2B7CC2] font-semibold mt-0.5">
                                            Kelas: {{ item.siswa.kelas_saat_ini }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <div class="flex items-center justify-center gap-3">
                                            <!-- Thumbnail Foto Lama -->
                                            <div class="flex flex-col items-center">
                                                <div class="w-11 h-14 rounded border border-slate-300 bg-slate-100 overflow-hidden shadow-2xs flex items-center justify-center">
                                                    <img
                                                        v-if="item.existingPhotoUrl"
                                                        :src="item.existingPhotoUrl"
                                                        :alt="`Foto Lama ${item.siswa.nama_lengkap}`"
                                                        class="w-full h-full object-cover"
                                                    />
                                                    <Camera v-else class="w-4 h-4 text-slate-400" />
                                                </div>
                                                <span class="text-[9.5px] font-semibold text-slate-500 mt-1">Foto Lama</span>
                                            </div>

                                            <ArrowRight class="w-4 h-4 text-[#2B7CC2] shrink-0" />

                                            <!-- Thumbnail Foto Baru dari ZIP -->
                                            <div class="flex flex-col items-center">
                                                <div class="w-11 h-14 rounded border-2 border-[#2B7CC2] bg-[#EAF4FC] overflow-hidden shadow-2xs flex items-center justify-center">
                                                    <img
                                                        v-if="item.newPhotoPreviewUrl"
                                                        :src="item.newPhotoPreviewUrl"
                                                        :alt="`Foto Baru ${item.siswa.nama_lengkap}`"
                                                        class="w-full h-full object-cover"
                                                    />
                                                    <Camera v-else class="w-4 h-4 text-[#2B7CC2]" />
                                                </div>
                                                <span class="text-[9.5px] font-bold text-[#2B7CC2] mt-1">Foto Baru</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-mono text-[11px] text-[#0F3A5F]">
                                        {{ item.entry.basename }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- BAGIAN 2: DAFTAR BERKAS DENGAN TIPE FILE KELIRU / TIDAK SESUAI -->
                <div v-if="invalidTypeModalItems.length > 0" class="space-y-3">
                    <div class="p-3.5 rounded-[10px] bg-rose-50 border border-rose-200 text-rose-950 flex items-start gap-3">
                        <FileWarning class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
                        <div class="space-y-1">
                            <p class="font-bold text-rose-900">
                                Ditemukan {{ invalidTypeModalItems.length }} berkas dengan tipe file yang keliru / tidak sesuai!
                            </p>
                            <p class="text-rose-800 leading-relaxed">
                                Sistem hanya menerima file gambar dengan format <strong>.JPG, .JPEG, .PNG, atau .WEBP</strong> yang valid. Berkas di bawah ini memiliki format yang keliru atau rusak sehingga <strong>ditolak dan tidak akan disimpan</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="border border-rose-200 rounded-[8px] overflow-hidden max-h-48 overflow-y-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-rose-900 text-white text-[11px] uppercase font-bold sticky top-0">
                                <tr>
                                    <th class="px-3 py-2.5 w-10 text-center">No</th>
                                    <th class="px-3 py-2.5">Nama Berkas</th>
                                    <th class="px-3 py-2.5 w-36">Tipe Terdeteksi</th>
                                    <th class="px-3 py-2.5">Keterangan Kesalahan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-rose-100 bg-white">
                                <tr
                                    v-for="(item, idx) in invalidTypeModalItems"
                                    :key="`inv-${item.filename}-${idx}`"
                                    class="hover:bg-rose-50/50"
                                >
                                    <td class="px-3 py-2 text-center text-[#64748B] font-mono">
                                        {{ idx + 1 }}
                                    </td>
                                    <td class="px-3 py-2 font-mono font-bold text-[#0F3A5F]">
                                        {{ item.basename }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300 font-mono">
                                            {{ item.ext }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-rose-700 font-medium">
                                        {{ item.reason }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- BAGIAN 3: DAFTAR FOTO DENGAN NIS / NISN TIDAK TERDAFTAR -->
                <div v-if="unmatchedModalItems.length > 0" class="space-y-3">
                    <div class="p-3.5 rounded-[10px] bg-amber-50 border border-amber-200 text-amber-950 flex items-start gap-3">
                        <AlertTriangle class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                        <div class="space-y-1">
                            <p class="font-bold text-amber-900">
                                Ditemukan {{ unmatchedModalItems.length }} foto dengan NIS / NISN yang tidak ada di Direktori Siswa.
                            </p>
                            <p class="text-amber-800 leading-relaxed">
                                Agar server tidak menyimpan foto yang tidak jelas, berkas foto dengan NIS/NISN di bawah ini <strong>akan diabaikan dan TIDAK disimpan ke server</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="border border-[#E2E8F0] rounded-[8px] overflow-hidden max-h-48 overflow-y-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-[#0F3A5F] text-white text-[11px] uppercase font-bold sticky top-0">
                                <tr>
                                    <th class="px-3 py-2.5 w-10 text-center">No</th>
                                    <th class="px-3 py-2.5">NIS / NISN (Tidak Ada)</th>
                                    <th class="px-3 py-2.5">Nama Berkas di ZIP</th>
                                    <th class="px-3 py-2.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2E8F0] bg-white">
                                <tr
                                    v-for="(item, idx) in unmatchedModalItems"
                                    :key="`unm-${item.filename}-${idx}`"
                                    class="hover:bg-amber-50/40"
                                >
                                    <td class="px-3 py-2 text-center text-[#64748B] font-mono">
                                        {{ idx + 1 }}
                                    </td>
                                    <td class="px-3 py-2 font-mono font-bold text-rose-600">
                                        {{ item.identifier }}
                                    </td>
                                    <td class="px-3 py-2 font-mono text-[#0F3A5F]">
                                        {{ item.basename }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                            NIS Tidak Ada (Diabaikan)
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Ringkasan Foto Valid yang Siap Disimpan -->
                <div
                    v-if="zipInspection"
                    class="p-3 rounded-[8px] border flex items-center justify-between"
                    :class="zipInspection.matched.length > 0 ? 'bg-emerald-50/70 border-emerald-200 text-emerald-950' : 'bg-slate-50 border-slate-200 text-slate-600'"
                >
                    <div class="flex items-center gap-2">
                        <CheckCircle2 v-if="zipInspection.matched.length > 0" class="w-4 h-4 text-emerald-600 shrink-0" />
                        <Info v-else class="w-4 h-4 text-slate-500 shrink-0" />
                        <span v-if="zipInspection.matched.length > 0">
                            Ringkasan Foto Valid: <strong>{{ zipInspection.newPhotos.length }} Foto Siswa Baru</strong> (belum punya foto) dan
                            <strong>{{ zipInspection.existingPhotos.length }} Foto Siswa Lama</strong> (sudah punya foto sebelumnya).
                        </span>
                        <span v-else>
                            <strong>Tidak ada satu pun foto valid</strong> yang dapat diproses dari arsip ZIP ini. Silakan perbaiki berkas Anda.
                        </span>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex flex-wrap items-center justify-end gap-2 w-full">
                    <BaseButton
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="isUnmatchedNisModalOpen = false"
                    >
                        {{ zipInspection && zipInspection.matched.length > 0 ? 'Batal' : 'Tutup' }}
                    </BaseButton>

                    <!-- Jika ada siswa yang sudah punya foto sebelumnya: tampilkan opsi 'Lewati yang Sudah Punya Foto' & 'Ganti Semua Foto Lama' -->
                    <template v-if="zipInspection && existingPhotoModalItems.length > 0">
                        <BaseButton
                            v-if="zipInspection.newPhotos.length > 0"
                            type="button"
                            variant="outline"
                            size="sm"
                            :loading="isCompressingFoto || fotoForm.processing"
                            @click="executeCompressAndUploadFoto('skip_existing')"
                        >
                            <UserCheck class="w-4 h-4 mr-1 text-[#2B7CC2]" />
                            <span>Lewati yang Sudah Punya Foto (Simpan {{ zipInspection.newPhotos.length }} Foto Baru)</span>
                        </BaseButton>

                        <BaseButton
                            type="button"
                            variant="primary"
                            size="sm"
                            :loading="isCompressingFoto || fotoForm.processing"
                            @click="executeCompressAndUploadFoto('overwrite_all')"
                        >
                            <RefreshCw class="w-3.5 h-3.5 mr-1.5" />
                            <span>Ganti Semua Foto Lama (Simpan {{ zipInspection.matched.length }} Foto)</span>
                        </BaseButton>
                    </template>

                    <!-- Jika tidak ada foto lama (hanya ada temuan tipe file keliru / NIS tidak ada), tampilkan tombol Simpan Foto Valid -->
                    <BaseButton
                        v-else-if="zipInspection && zipInspection.matched.length > 0"
                        type="button"
                        variant="primary"
                        size="sm"
                        :loading="isCompressingFoto || fotoForm.processing"
                        @click="executeCompressAndUploadFoto('overwrite_all')"
                    >
                        <Camera class="w-4 h-4 mr-1" />
                        <span>Simpan & Kompres {{ zipInspection.matched.length }} Foto Valid Saja</span>
                    </BaseButton>
                </div>
            </template>
        </BaseModal>

        <!-- Modal Konfirmasi Validasi Import -->
        <BaseConfirmModal
            :open="isConfirmOpen"
            :loading="isConfirmLoading"
            :title="confirmOptions.title"
            :message="confirmOptions.message"
            :item-info="confirmOptions.itemInfo"
            :sub-info="confirmOptions.subInfo"
            :confirm-text="confirmOptions.confirmText"
            :cancel-text="confirmOptions.cancelText"
            :variant="confirmOptions.variant"
            @close="closeConfirm"
            @confirm="handleConfirm"
        />
    </div>
</template>
