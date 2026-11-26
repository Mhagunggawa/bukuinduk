<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Download,
    Printer,
    Edit3,
    BookOpen,
    Save,
    X,
    UserCheck,
    Calendar,
    GraduationCap,
} from '@lucide/vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseCard from '@/components/base/BaseCard.vue';
import BaseBadge from '@/components/base/BaseBadge.vue';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseInput from '@/components/base/BaseInput.vue';
import BaseSelect from '@/components/base/BaseSelect.vue';

interface Mapel {
    id: number;
    kode_mapel: string;
    nama_mapel: string;
    kelompok: 'A_UMUM' | 'B_KEJURUAN';
    sub_kelompok: string | null;
    no_urut: number;
}

interface Ekskul {
    id: number;
    kode_ekskul: string;
    nama_ekskul: string;
    no_urut: number;
}

interface TahunPelajaran {
    id: number;
    tahun_pelajaran: string;
}

interface NilaiAkademikItem {
    id: number;
    mata_pelajaran_id: number;
    tahun_pelajaran_id: number | null;
    semester_ke: number;
    nilai_angka: number | null;
    keterangan: string | null;
    mata_pelajaran?: Mapel;
}

interface NilaiEkskulItem {
    id: number;
    ekstrakurikuler_id: number;
    tahun_pelajaran_id: number | null;
    semester_ke: number;
    nilai_predikat: string | null;
    keterangan: string | null;
}

interface RekapSemesterItem {
    id: number;
    semester_ke: number;
    tahun_pelajaran_id: number | null;
    nilai_pkl: string | null;
    keterangan_pkl: string | null;
    jumlah_nilai: number | null;
    nilai_rata_rata: number | null;
    ranking: number | null;
    sakit_hari: number;
    izin_hari: number;
    tanpa_kabar_hari: number;
    jumlah_absen_hari: number;
    status_kenaikan: 'NAIK_TINGKAT' | 'TIDAK_NAIK' | 'LULUS' | null;
    tingkat_tujuan: string | null;
    tahun_pelajaran?: TahunPelajaran;
}

interface SiswaDetail {
    id: number;
    nis: string;
    nisn: string;
    nama_lengkap: string;
    jenis_kelamin: 'L' | 'P';
    kelas_saat_ini: string | null;
    tempat_lahir: string | null;
    tanggal_lahir: string | null;
    warga_negara: string | null;
    agama: string | null;
    alamat_siswa: string | null;
    nama_orang_tua: string | null;
    pekerjaan_orang_tua: string | null;
    alamat_orang_tua: string | null;
    diterima_tanggal: string | null;
    diterima_asal_sekolah: string | null;
    diterima_nomor_sttb: string | null;
    diterima_tanggal_sttb: string | null;
    keluar_tanggal: string | null;
    keluar_alasan: string | null;
    tamat_nomor_sttb: string | null;
    tamat_tanggal_sttb: string | null;
    keterangan_lain_lain: string | null;
    pasfoto_url: string | null;
    status_siswa: 'AKTIF' | 'TAMAT' | 'KELUAR';
    konsentrasi_keahlian?: {
        nama_konsentrasi: string;
        kode_singkat: string;
        program_keahlian?: {
            nama_program_keahlian: string;
            bidang_studi?: {
                nama_bidang_studi: string;
            };
        };
    };
    nilai_akademik: NilaiAkademikItem[];
    nilai_ekstrakurikuler: NilaiEkskulItem[];
    rekap_semester: RekapSemesterItem[];
}

const props = defineProps<{
    siswa: SiswaDetail;
    mapelUmum: Mapel[];
    mapelKejuruan: Mapel[];
    ekskulList: Ekskul[];
    tahunList: TahunPelajaran[];
    jurusanList: any[];
}>();

// Download PDF Action (Hanya bagian HASIL PRESTASI PENILAIAN ukuran F4 Landscape)
const pdfDownloadUrl = computed(() => {
    const slotsParam = slotTahunIds.value.join(',');
    return `/siswa/${props.siswa.id}/buku-induk/pdf?slots=${encodeURIComponent(slotsParam)}`;
});

const handleDownloadPdf = () => {
    window.location.href = pdfDownloadUrl.value;
};

// Print Action
const handlePrint = () => {
    window.print();
};

// 10 Semesters list
const semesters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
const romanSemesters = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

// 5 Slot Tahun Pelajaran untuk 5 Tingkat Kelas (Tiap Slot = 2 Semester: Ganjil & Genap)
const computeInitialSlotTahunIds = (): (number | '')[] => {
    const slots: (number | '')[] = ['', '', '', '', ''];
    const rekapList = props.siswa.rekap_semester || [];
    const akademikList = props.siswa.nilai_akademik || [];

    let baseTahunIdx = 0;
    if (props.siswa.diterima_tanggal && props.tahunList.length > 0) {
        const entryYear = props.siswa.diterima_tanggal.substring(0, 4);
        const foundIdx = props.tahunList.findIndex((t) => t.tahun_pelajaran.startsWith(entryYear));
        if (foundIdx !== -1) {
            baseTahunIdx = foundIdx;
        }
    }

    for (let gIdx = 0; gIdx < 5; gIdx++) {
        const semOdd = gIdx * 2 + 1;
        const semEven = semOdd + 1;

        const existingRekap = rekapList.find(
            (r) => (r.semester_ke === semOdd || r.semester_ke === semEven) && r.tahun_pelajaran_id
        );
        const existingAkademik = akademikList.find(
            (a) => (a.semester_ke === semOdd || a.semester_ke === semEven) && a.tahun_pelajaran_id
        );

        if (existingRekap?.tahun_pelajaran_id) {
            slots[gIdx] = existingRekap.tahun_pelajaran_id;
        } else if (existingAkademik?.tahun_pelajaran_id) {
            slots[gIdx] = existingAkademik.tahun_pelajaran_id;
        } else if (props.tahunList[baseTahunIdx + gIdx]) {
            slots[gIdx] = props.tahunList[baseTahunIdx + gIdx].id;
        } else if (props.tahunList[gIdx]) {
            slots[gIdx] = props.tahunList[gIdx].id;
        } else if (props.tahunList[0]) {
            slots[gIdx] = props.tahunList[0].id;
        }
    }

    return slots;
};

const slotTahunIds = ref<(number | '')[]>(computeInitialSlotTahunIds());

const getTahunIdForSem = (sem: number): number | '' => {
    const slotIdx = Math.floor((sem - 1) / 2);
    return slotTahunIds.value[slotIdx] ?? '';
};

// Maps keyed by Tahun Pelajaran + Semester so different Tahun Pelajaran store & display different grades
const academicByTahunMap = computed(() => {
    const map: Record<string, number | null> = {};
    for (const item of props.siswa.nilai_akademik || []) {
        if (item.tahun_pelajaran_id) {
            map[`${item.tahun_pelajaran_id}_${item.mata_pelajaran_id}_${item.semester_ke}`] = item.nilai_angka;
        }
    }
    return map;
});

const ekskulByTahunMap = computed(() => {
    const map: Record<string, string | null> = {};
    for (const item of props.siswa.nilai_ekstrakurikuler || []) {
        if (item.tahun_pelajaran_id) {
            map[`${item.tahun_pelajaran_id}_${item.ekstrakurikuler_id}_${item.semester_ke}`] = item.nilai_predikat;
        }
    }
    return map;
});

const rekapByTahunMap = computed(() => {
    const map: Record<string, RekapSemesterItem> = {};
    for (const item of props.siswa.rekap_semester || []) {
        if (item.tahun_pelajaran_id) {
            map[`${item.tahun_pelajaran_id}_${item.semester_ke}`] = item;
        }
    }
    return map;
});

// Quick Lookup Maps for matrix rendering (reactive to selected Tahun Pelajaran per slot)
const academicMap = computed(() => {
    const map: Record<string, number | null> = {};
    for (const m of [...props.mapelUmum, ...props.mapelKejuruan]) {
        for (const sem of semesters) {
            const tpId = getTahunIdForSem(sem);
            if (tpId) {
                map[`${m.id}_${sem}`] = academicByTahunMap.value[`${tpId}_${m.id}_${sem}`] ?? null;
            }
        }
    }
    return map;
});

const ekskulMap = computed(() => {
    const map: Record<string, string | null> = {};
    for (const e of props.ekskulList) {
        for (const sem of semesters) {
            const tpId = getTahunIdForSem(sem);
            if (tpId) {
                map[`${e.id}_${sem}`] = ekskulByTahunMap.value[`${tpId}_${e.id}_${sem}`] ?? null;
            }
        }
    }
    return map;
});

const rekapMap = computed(() => {
    const map: Record<number, RekapSemesterItem | undefined> = {};
    for (const sem of semesters) {
        const tpId = getTahunIdForSem(sem);
        if (tpId) {
            map[sem] = rekapByTahunMap.value[`${tpId}_${sem}`];
        }
    }
    return map;
});

// Format dates nicely
const formatDate = (dateStr?: string | null) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    } catch (e) {
        return dateStr;
    }
};

// 5 Kelompok Label Tahun Pelajaran untuk Cetak & Header
const gradeYears = computed(() => {
    return [0, 1, 2, 3, 4].map((gIdx) => {
        const id = slotTahunIds.value[gIdx];
        const found = props.tahunList.find((t) => Number(t.id) === Number(id));
        if (found) return found.tahun_pelajaran;

        const diterima = props.siswa.diterima_tanggal;
        let baseYear = 2024;
        if (diterima) {
            const parsed = parseInt(diterima.substring(0, 4), 10);
            if (!isNaN(parsed)) baseYear = parsed;
        }
        return `${baseYear + gIdx}/${baseYear + gIdx + 1}`;
    });
});

// Modal Input/Edit Nilai Semester
const isNilaiModalOpen = ref(false);
const selectedSemester = ref(1);

const nilaiForm = useForm({
    semester_ke: 1,
    tahun_pelajaran_id: '',
    nilai_akademik: [] as { mata_pelajaran_id: number; nilai_angka: string | number; keterangan: string }[],
    nilai_ekskul: [] as { ekstrakurikuler_id: number; nilai_predikat: string; keterangan: string }[],
    nilai_pkl: '',
    keterangan_pkl: '',
    ranking: '',
    sakit_hari: 0,
    izin_hari: 0,
    tanpa_kabar_hari: 0,
    status_kenaikan: '',
    tingkat_tujuan: '',
});

const populateModalFields = (sem: number, tpId: string | number) => {
    const currentRekap = tpId ? rekapByTahunMap.value[`${tpId}_${sem}`] : undefined;
    nilaiForm.nilai_pkl = currentRekap?.nilai_pkl || '';
    nilaiForm.keterangan_pkl = currentRekap?.keterangan_pkl || '';
    nilaiForm.ranking = currentRekap?.ranking ? String(currentRekap.ranking) : '';
    nilaiForm.sakit_hari = currentRekap?.sakit_hari ?? 0;
    nilaiForm.izin_hari = currentRekap?.izin_hari ?? 0;
    nilaiForm.tanpa_kabar_hari = currentRekap?.tanpa_kabar_hari ?? 0;
    nilaiForm.status_kenaikan = currentRekap?.status_kenaikan || '';
    nilaiForm.tingkat_tujuan = currentRekap?.tingkat_tujuan || '';

    const allMapel = [...props.mapelUmum, ...props.mapelKejuruan];
    nilaiForm.nilai_akademik = allMapel.map((m) => {
        const val = tpId ? academicByTahunMap.value[`${tpId}_${m.id}_${sem}`] : null;
        return {
            mata_pelajaran_id: m.id,
            nilai_angka: val !== null && val !== undefined ? String(val) : '',
            keterangan: 'Tuntas',
        };
    });

    nilaiForm.nilai_ekskul = props.ekskulList.map((e) => {
        const val = tpId ? ekskulByTahunMap.value[`${tpId}_${e.id}_${sem}`] : null;
        return {
            ekstrakurikuler_id: e.id,
            nilai_predikat: val || '',
            keterangan: '',
        };
    });
};

const openNilaiModal = (sem: number = 1) => {
    selectedSemester.value = sem;
    nilaiForm.semester_ke = sem;

    const slotTpId = getTahunIdForSem(sem);
    const defaultTpId = slotTpId || props.tahunList[0]?.id || '';
    nilaiForm.tahun_pelajaran_id = defaultTpId ? String(defaultTpId) : '';

    populateModalFields(sem, nilaiForm.tahun_pelajaran_id);
    isNilaiModalOpen.value = true;
};

const onModalSemesterChange = () => {
    nilaiForm.semester_ke = selectedSemester.value;
    const slotTpId = getTahunIdForSem(selectedSemester.value);
    if (slotTpId) {
        nilaiForm.tahun_pelajaran_id = String(slotTpId);
    }
    populateModalFields(selectedSemester.value, nilaiForm.tahun_pelajaran_id);
};

const onModalTahunChange = () => {
    populateModalFields(selectedSemester.value, nilaiForm.tahun_pelajaran_id);
};

const closeNilaiModal = () => {
    isNilaiModalOpen.value = false;
};

const submitNilaiForm = () => {
    nilaiForm.post(`/siswa/${props.siswa.id}/nilai-semester`, {
        onSuccess: () => {
            const slotIdx = Math.floor((selectedSemester.value - 1) / 2);
            if (nilaiForm.tahun_pelajaran_id) {
                slotTahunIds.value[slotIdx] = Number(nilaiForm.tahun_pelajaran_id);
            }
            closeNilaiModal();
        },
    });
};
</script>

<template>
    <Head :title="`Buku Induk - ${siswa.nama_lengkap}`" />
    <div class="space-y-5">
        <!-- Action Bar Atas Kanvas (Disembunyikan saat Print) -->
        <BaseCard class="p-4 print:hidden">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <BaseButton
                        variant="secondary"
                        size="sm"
                        as="a"
                        href="/siswa"
                    >
                        <ArrowLeft class="w-4 h-4 mr-1.5" />
                        Kembali ke Direktori
                    </BaseButton>

                    <div class="h-5 w-px bg-[#E2E8F0] hidden sm:block"></div>

                    <div class="hidden sm:flex items-center gap-2">
                        <span class="text-xs text-[#64748B]">Buku Induk:</span>
                        <span class="text-xs font-semibold text-[#0F3A5F]">{{ siswa.nama_lengkap }} (NIS: {{ siswa.nis }})</span>
                        <BaseBadge
                            :variant="siswa.status_siswa === 'AKTIF' ? 'success' : 'neutral'"
                            size="sm"
                            dot
                        >
                            {{ siswa.status_siswa }}
                        </BaseBadge>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Dropdown Pilih Semester untuk input nilai -->
                    <BaseButton
                        variant="outline"
                        size="sm"
                        @click="openNilaiModal(1)"
                    >
                        <Edit3 class="w-3.5 h-3.5 mr-1.5 text-[#2B7CC2]" />
                        Input / Edit Nilai Semester
                    </BaseButton>

                    <!-- Tombol Cetak Langsung -->
                    <BaseButton
                        variant="secondary"
                        size="sm"
                        @click="handlePrint"
                    >
                        <Printer class="w-4 h-4 mr-1.5 text-[#64748B]" />
                        Cetak
                    </BaseButton>

                    <!-- Tombol Download PDF Khusus Bagian Hasil Prestasi Penilaian -->
                    <BaseButton
                        variant="primary"
                        size="sm"
                        @click="handleDownloadPdf"
                    >
                        <Download class="w-4 h-4 mr-1.5" />
                        Download PDF
                    </BaseButton>
                </div>
            </div>
        </BaseCard>

        <!-- Kanvas Fisik Buku Induk (Dioptimalkan untuk Layar & @media print F4/A3 Landscape) -->
        <div class="buku-induk-canvas bg-white border border-[#E2E8F0] rounded-xl p-6 shadow-xs text-slate-800 mx-auto overflow-x-auto">
            <!-- Header Lembar Buku Induk -->
            <div class="border-b-2 border-[#0F3A5F] pb-3 mb-4 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                <div class="flex-1">
                    <h1 class="text-xl sm:text-2xl font-black uppercase tracking-wider text-[#0F3A5F] leading-tight">
                        HASIL PRESTASI PENILAIAN
                    </h1>
                    <div class="text-[11px] font-bold tracking-widest text-[#64748B] uppercase mt-0.5">
                        BUKU INDUK SISWA SEKOLAH MENENGAH KEJURUAN (SMK)
                    </div>
                </div>

                <!-- Info Jurusan Header -->
                <div class="text-[11px] space-y-0.5 text-right font-semibold text-slate-700">
                    <div>
                        <span class="text-slate-400 uppercase">BIDANG STUDI KEAHLIAN:</span>
                        <span class="ml-1 uppercase font-bold text-[#0F3A5F]">
                            {{ siswa.konsentrasi_keahlian?.program_keahlian?.bidang_studi?.nama_bidang_studi || 'TEKNOLOGI INFORMASI' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 uppercase">PROGRAM KEAHLIAN:</span>
                        <span class="ml-1 uppercase font-bold text-[#0F3A5F]">
                            {{ siswa.konsentrasi_keahlian?.program_keahlian?.nama_program_keahlian || 'TEKNIK JARINGAN KOMPUTER & TELEKOMUNIKASI' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 uppercase">KONSENTRASI KEAHLIAN:</span>
                        <span class="ml-1 uppercase font-bold text-[#0F3A5F]">
                            {{ siswa.konsentrasi_keahlian?.nama_konsentrasi || 'TEKNIK KOMPUTER DAN JARINGAN (TKJ)' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Konten Utama: 2 Panel Berdampingan (Kiri 32%, Kanan 68%) -->
            <div class="grid grid-cols-12 gap-4 items-start">
                <!-- ================= PANEL KIRI (32% - IDENTITAS SISWA) ================= -->
                <div class="col-span-12 lg:col-span-4 panel-kiri border border-slate-300 rounded-lg p-3.5 text-[11px] bg-white space-y-3 font-sans">
                    <div class="bg-[#0F3A5F] text-white font-bold text-[11px] tracking-wider uppercase text-center py-1.5 rounded-md">
                        I. IDENTITAS SISWA
                    </div>

                    <!-- Biodata Siswa 1-9 -->
                    <table class="w-full text-left leading-tight border-collapse">
                        <tbody>
                            <tr class="border-b border-slate-200">
                                <td class="w-32 py-1 font-semibold text-slate-600">1. NIS</td>
                                <td class="w-3 text-center text-slate-400">:</td>
                                <td class="py-1 font-mono font-bold text-[#0F3A5F]">{{ siswa.nis }}</td>
                            </tr>
                            <tr class="border-b border-slate-200">
                                <td class="py-1 font-semibold text-slate-600">2. NISN</td>
                                <td class="text-center text-slate-400">:</td>
                                <td class="py-1 font-mono font-bold text-[#0F3A5F]">{{ siswa.nisn }}</td>
                            </tr>
                            <tr class="border-b border-slate-200">
                                <td class="py-1 font-semibold text-slate-600">3. Jenis Kelamin</td>
                                <td class="text-center text-slate-400">:</td>
                                <td class="py-1 font-semibold text-[#0F3A5F]">
                                    {{ siswa.jenis_kelamin === 'L' ? 'LAKI-LAKI' : 'PEREMPUAN' }}
                                </td>
                            </tr>
                            <tr class="border-b border-slate-200">
                                <td class="py-1 font-semibold text-slate-600">4. Nama Lengkap</td>
                                <td class="text-center text-slate-400">:</td>
                                <td class="py-1 font-bold text-[#0F3A5F] uppercase">{{ siswa.nama_lengkap }}</td>
                            </tr>
                            <tr class="border-b border-slate-200">
                                <td class="py-1 font-semibold text-slate-600">5. Kelas Saat Ini</td>
                                <td class="text-center text-slate-400">:</td>
                                <td class="py-1 font-bold text-[#0F3A5F]">{{ siswa.kelas_saat_ini || '-' }}</td>
                            </tr>
                            <tr class="border-b border-slate-200">
                                <td class="py-1 font-semibold text-slate-600">6. Tempat, Tgl Lahir</td>
                                <td class="text-center text-slate-400">:</td>
                                <td class="py-1 text-slate-800">
                                    {{ siswa.tempat_lahir || '-' }}, {{ formatDate(siswa.tanggal_lahir) }}
                                </td>
                            </tr>
                            <tr class="border-b border-slate-200">
                                <td class="py-1 font-semibold text-slate-600">7. Warga Negara</td>
                                <td class="text-center text-slate-400">:</td>
                                <td class="py-1 text-slate-800">{{ siswa.warga_negara || 'WNI' }}</td>
                            </tr>
                            <tr class="border-b border-slate-200">
                                <td class="py-1 font-semibold text-slate-600">8. Agama</td>
                                <td class="text-center text-slate-400">:</td>
                                <td class="py-1 text-slate-800">{{ siswa.agama || '-' }}</td>
                            </tr>
                            <tr class="border-b border-slate-200">
                                <td class="py-1 font-semibold text-slate-600 align-top">9. Alamat Siswa</td>
                                <td class="text-center text-slate-400 align-top">:</td>
                                <td class="py-1 text-slate-800 leading-snug">{{ siswa.alamat_siswa || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Sub-bagian 10: Orang Tua -->
                    <div class="border-t border-slate-200 pt-2.5">
                        <div class="font-bold text-[#0F3A5F] uppercase tracking-wide text-[11px] mb-1.5">
                            10. DATA ORANG TUA / WALI
                        </div>
                        <div class="space-y-1 pl-1 text-[10.5px]">
                            <div>
                                <span class="font-medium text-slate-600">Nama Orang Tua:</span>
                                <span class="font-bold text-[#0F3A5F] ml-1">{{ siswa.nama_orang_tua || '-' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-slate-600">a. Pekerjaan:</span>
                                <span class="text-slate-800 ml-1">{{ siswa.pekerjaan_orang_tua || '-' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-slate-600 align-top">b. Alamat Rumah:</span>
                                <span class="text-slate-800 ml-1 leading-tight">{{ siswa.alamat_orang_tua || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sub-bagian 11: Diterima Menjadi Siswa -->
                    <div class="border-t border-slate-200 pt-2.5">
                        <div class="font-bold text-[#0F3A5F] uppercase tracking-wide text-[11px] mb-1.5">
                            11. DITERIMA MENJADI SISWA
                        </div>
                        <div class="space-y-1 pl-1 text-[10.5px]">
                            <div>
                                <span class="font-medium text-slate-600">a. Mulai Tanggal:</span>
                                <span class="text-slate-800 ml-1 font-medium">{{ formatDate(siswa.diterima_tanggal) }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-slate-600">b. Asal Sekolah:</span>
                                <span class="text-slate-800 ml-1 font-semibold">{{ siswa.diterima_asal_sekolah || '-' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-slate-600">c. Nomor STTB/Ijazah:</span>
                                <span class="text-slate-800 ml-1 font-mono">{{ siswa.diterima_nomor_sttb || '-' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-slate-600">d. Tanggal STTB/Ijazah:</span>
                                <span class="text-slate-800 ml-1">{{ formatDate(siswa.diterima_tanggal_sttb) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sub-bagian 12: Meninggalkan Sekolah -->
                    <div class="border-t border-slate-200 pt-2.5">
                        <div class="font-bold text-[#0F3A5F] uppercase tracking-wide text-[11px] mb-1.5">
                            12. MENINGGALKAN SEKOLAH
                        </div>
                        <div class="space-y-1 pl-1 text-[10.5px]">
                            <div>
                                <span class="font-medium text-slate-600">a. Tanggal:</span>
                                <span class="text-slate-800 ml-1">{{ formatDate(siswa.keluar_tanggal) }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-slate-600">b. Alasan:</span>
                                <span class="text-slate-800 ml-1 italic">{{ siswa.keluar_alasan || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sub-bagian 13: Tamat Belajar -->
                    <div class="border-t border-slate-200 pt-2.5">
                        <div class="font-bold text-[#0F3A5F] uppercase tracking-wide text-[11px] mb-1.5">
                            13. TAMAT BELAJAR / LULUS
                        </div>
                        <div class="space-y-1 pl-1 text-[10.5px]">
                            <div>
                                <span class="font-medium text-slate-600">a. Nomor STTB/Ijazah:</span>
                                <span class="text-slate-800 ml-1 font-mono font-semibold">{{ siswa.tamat_nomor_sttb || '-' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-slate-600">b. Tanggal STTB/Ijazah:</span>
                                <span class="text-slate-800 ml-1 font-medium">{{ formatDate(siswa.tamat_tanggal_sttb) }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-slate-600">Keterangan Lain-Lain:</span>
                                <span class="text-slate-800 ml-1">{{ siswa.keterangan_lain_lain || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kotak Bingkai PasPhoto Ukuran Proporsional 3x4 cm di Bagian Kiri Bawah -->
                    <div class="border-t border-slate-200 pt-3 flex flex-col items-center">
                        <div class="pasphoto-frame w-[3cm] h-[4cm] border border-dashed border-slate-300 rounded-md bg-slate-50/70 flex flex-col items-center justify-center p-1 relative shadow-xs overflow-hidden">
                            <img
                                v-if="siswa.pasfoto_url"
                                :src="siswa.pasfoto_url"
                                :alt="siswa.nama_lengkap"
                                class="w-full h-full object-cover rounded-xs"
                            />
                            <div v-else class="text-center p-2 text-slate-400">
                                <UserCheck class="w-8 h-8 mx-auto mb-1 text-slate-300" />
                                <span class="text-[10px] font-semibold block uppercase leading-tight text-slate-400">Pasphoto</span>
                                <span class="text-[9px] block text-slate-400">3 x 4 cm</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-medium text-slate-500 mt-1 uppercase">Pasfoto Siswa 3x4</span>
                    </div>
                </div>

                <!-- ================= PANEL KANAN (68% - TABEL MATRIKS NILAI SEMESTER I - X) ================= -->
                <div class="col-span-12 lg:col-span-8 panel-kanan border border-slate-300 rounded-lg overflow-x-auto bg-white">
                    <table class="w-full text-center border-collapse text-[10.5px]">
                        <!-- Baris 1 & 2: Header Kolom Bertingkat -->
                        <thead>
                            <tr class="bg-[#0F3A5F] text-white font-bold text-[10px]">
                                <th rowspan="2" class="border border-slate-300 px-1.5 py-1.5 w-6">NO</th>
                                <th rowspan="2" class="border border-slate-300 px-2 py-1.5 text-left min-w-[180px]">
                                    PROGRAM PENDIDIKAN DAN PELATIHAN
                                </th>
                                <!-- 5 Kelompok Tahun Pelajaran (Colspan 2 per Tingkat Kelas: X, XI, XII, XIII, XIV) -->
                                <th
                                    v-for="(tpYear, gIdx) in gradeYears"
                                    :key="`thn-grade-${gIdx}`"
                                    colspan="2"
                                    class="border border-slate-300 px-1 py-1 text-[9.5px] font-semibold whitespace-nowrap text-center"
                                >
                                    <!-- Pemilih Tahun Pelajaran di Layar (Jika diganti maka nilai otomatis berubah sesuai T.P.) -->
                                    <select
                                        v-if="tahunList.length > 0"
                                        v-model="slotTahunIds[gIdx]"
                                        class="print:hidden bg-[#134571] hover:bg-[#1C5B94] text-white font-bold text-[9.5px] rounded px-1 py-0.5 border border-white/25 focus:outline-none focus:ring-1 focus:ring-sky-300 cursor-pointer"
                                        :title="`Pilih Tahun Pelajaran untuk Semester ${romanSemesters[gIdx * 2]} & ${romanSemesters[gIdx * 2 + 1]}`"
                                    >
                                        <option v-for="t in tahunList" :key="t.id" :value="t.id" class="bg-white text-[#0F3A5F]">
                                            T.P. {{ t.tahun_pelajaran }}
                                        </option>
                                    </select>
                                    <span :class="tahunList.length > 0 ? 'hidden print:inline' : ''">
                                        {{ tpYear }}
                                    </span>
                                </th>
                                <th rowspan="2" class="border border-slate-300 px-1 py-1.5 w-10 text-[9px] vertical-text">
                                    KET
                                </th>
                            </tr>
                            <tr class="bg-[#134571] text-white font-bold text-[10px]">
                                <th
                                    v-for="(rom, idx) in romanSemesters"
                                    :key="`rom-${rom}`"
                                    class="border border-slate-300 px-1 py-1 text-[10px] cursor-pointer hover:bg-[#1C5B94] transition-colors"
                                    :title="`Klik untuk edit Nilai Semester ${rom}`"
                                    @click="openNilaiModal(idx + 1)"
                                >
                                    {{ rom }}
                                </th>
                            </tr>
                        </thead>

                        <tbody class="text-slate-800">
                            <!-- ================= SEKSI A. MATA PELAJARAN UMUM ================= -->
                            <tr class="bg-[#F1F5F9] text-[#0F3A5F] font-bold text-left">
                                <td class="border border-slate-300 px-1.5 py-1 text-center font-bold">A</td>
                                <td colspan="12" class="border border-slate-300 px-2 py-1 tracking-wider uppercase text-[10.5px]">
                                    KELOMPOK MATA PELAJARAN UMUM
                                </td>
                            </tr>

                            <tr
                                v-for="(mapel, mIdx) in mapelUmum"
                                :key="mapel.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <td class="border border-slate-300 px-1 py-1 font-semibold text-center text-slate-600">
                                    {{ mIdx + 1 }}
                                </td>
                                <td class="border border-slate-300 px-2 py-1 text-left font-medium text-slate-800">
                                    {{ mapel.nama_mapel }}
                                    <span v-if="mapel.kode_mapel === 'PAI'" class="text-[9px] text-slate-400 block italic">
                                        (Sesuai agama yang dianut siswa: {{ siswa.agama || 'Islam' }})
                                    </span>
                                </td>

                                <!-- Nilai Semester 1 - 10 -->
                                <td
                                    v-for="sem in semesters"
                                    :key="`val-${mapel.id}-${sem}`"
                                    class="border border-slate-300 px-1 py-1 font-mono text-[10px]"
                                    :class="academicMap[`${mapel.id}_${sem}`] ? 'font-bold text-[#0F3A5F]' : 'text-slate-300'"
                                >
                                    {{ academicMap[`${mapel.id}_${sem}`] ?? '-' }}
                                </td>

                                <td class="border border-slate-300 px-1 py-1 text-[9px] text-slate-400">
                                    -
                                </td>
                            </tr>

                            <!-- ================= SEKSI B. MATA PELAJARAN KEJURUAN ================= -->
                            <tr class="bg-[#F1F5F9] text-[#0F3A5F] font-bold text-left">
                                <td class="border border-slate-300 px-1.5 py-1 text-center font-bold">B</td>
                                <td colspan="12" class="border border-slate-300 px-2 py-1 tracking-wider uppercase text-[10.5px]">
                                    KELOMPOK MATA PELAJARAN KEJURUAN
                                </td>
                            </tr>

                            <tr
                                v-for="(mapel, mIdx) in mapelKejuruan"
                                :key="mapel.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <td class="border border-slate-300 px-1 py-1 font-semibold text-center text-slate-600">
                                    {{ mIdx + 1 }}
                                </td>
                                <td class="border border-slate-300 px-2 py-1 text-left font-medium text-slate-800">
                                    {{ mapel.nama_mapel }}
                                </td>

                                <!-- Nilai Semester 1 - 10 -->
                                <td
                                    v-for="sem in semesters"
                                    :key="`val-b-${mapel.id}-${sem}`"
                                    class="border border-slate-300 px-1 py-1 font-mono text-[10px]"
                                    :class="academicMap[`${mapel.id}_${sem}`] ? 'font-bold text-[#0F3A5F]' : 'text-slate-300'"
                                >
                                    {{ academicMap[`${mapel.id}_${sem}`] ?? '-' }}
                                </td>

                                <td class="border border-slate-300 px-1 py-1 text-[9px] text-slate-400">
                                    -
                                </td>
                            </tr>

                            <!-- ================= SEKSI PKL & EKSTRAKURIKULER ================= -->
                            <tr class="bg-[#F1F5F9] text-[#0F3A5F] font-bold text-left">
                                <td class="border border-slate-300 px-1.5 py-1 text-center font-bold">C</td>
                                <td colspan="12" class="border border-slate-300 px-2 py-1 uppercase text-[10px] tracking-wide">
                                    PRAKTIK KERJA LAPANGAN (PKL) & KEGIATAN EKSTRA KURIKULER
                                </td>
                            </tr>

                            <!-- Praktik Kerja Lapangan (PKL) -->
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="border border-slate-300 px-1 py-1 font-semibold text-center text-slate-600">1</td>
                                <td class="border border-slate-300 px-2 py-1 text-left font-semibold text-slate-800">
                                    Praktik Kerja Lapangan (PKL)
                                </td>
                                <td
                                    v-for="sem in semesters"
                                    :key="`pkl-${sem}`"
                                    class="border border-slate-300 px-1 py-1 font-bold text-[10px] text-[#0F3A5F]"
                                >
                                    {{ rekapMap[sem]?.nilai_pkl || '-' }}
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-[9px] text-slate-400">-</td>
                            </tr>

                            <!-- Ekstrakurikuler -->
                            <tr
                                v-for="(ekskul, eIdx) in ekskulList"
                                :key="ekskul.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <td class="border border-slate-300 px-1 py-1 font-semibold text-center text-slate-600">
                                    {{ eIdx + 2 }}
                                </td>
                                <td class="border border-slate-300 px-2 py-1 text-left font-medium text-slate-800">
                                    {{ ekskul.nama_ekskul }}
                                </td>
                                <td
                                    v-for="sem in semesters"
                                    :key="`ekskul-${ekskul.id}-${sem}`"
                                    class="border border-slate-300 px-1 py-1 font-bold text-[10px] text-[#0F3A5F]"
                                >
                                    {{ ekskulMap[`${ekskul.id}_${sem}`] || '-' }}
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-[9px] text-slate-400">-</td>
                            </tr>

                            <!-- ================= SEKSI REKAPITULASI BAWAH ================= -->
                            <!-- JUMLAH NILAI -->
                            <tr class="bg-[#F8FAFC] font-bold">
                                <td colspan="2" class="border border-slate-300 px-3 py-1 text-left uppercase text-[#0F3A5F] font-bold">
                                    JUMLAH NILAI
                                </td>
                                <td
                                    v-for="sem in semesters"
                                    :key="`jml-${sem}`"
                                    class="border border-slate-300 px-1 py-1 font-mono font-bold text-[#0F3A5F] text-[10px]"
                                >
                                    {{ rekapMap[sem]?.jumlah_nilai ?? '-' }}
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-[9px] text-slate-400">-</td>
                            </tr>

                            <!-- NILAI RATA-RATA -->
                            <tr class="bg-[#F1F5F9] font-bold">
                                <td colspan="2" class="border border-slate-300 px-3 py-1 text-left uppercase text-[#0F3A5F] font-bold">
                                    NILAI RATA-RATA
                                </td>
                                <td
                                    v-for="sem in semesters"
                                    :key="`avg-${sem}`"
                                    class="border border-slate-300 px-1 py-1 font-mono font-bold text-[#0F3A5F] text-[10px]"
                                >
                                    {{ rekapMap[sem]?.nilai_rata_rata ?? '-' }}
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-[9px] text-slate-400">-</td>
                            </tr>

                            <!-- RANKING -->
                            <tr class="bg-white">
                                <td colspan="2" class="border border-slate-300 px-3 py-1 text-left font-semibold text-slate-700">
                                    RANKING
                                </td>
                                <td
                                    v-for="sem in semesters"
                                    :key="`rank-${sem}`"
                                    class="border border-slate-300 px-1 py-1 font-mono font-bold text-[#0F3A5F] text-[10px]"
                                >
                                    {{ rekapMap[sem]?.ranking ?? '-' }}
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-[9px] text-slate-400">-</td>
                            </tr>

                            <!-- ABSENSI: SAKIT -->
                            <tr class="bg-white">
                                <td colspan="2" class="border border-slate-300 px-3 py-0.5 text-left font-medium text-slate-600">
                                    Ketidakhadiran: SAKIT (HARI)
                                </td>
                                <td
                                    v-for="sem in semesters"
                                    :key="`sakit-${sem}`"
                                    class="border border-slate-300 px-1 py-0.5 font-mono text-[10px] text-slate-700"
                                >
                                    {{ rekapMap[sem]?.sakit_hari ?? 0 }}
                                </td>
                                <td class="border border-slate-300 px-1 py-0.5 text-[9px] text-slate-400">-</td>
                            </tr>

                            <!-- ABSENSI: IZIN -->
                            <tr class="bg-white">
                                <td colspan="2" class="border border-slate-300 px-3 py-0.5 text-left font-medium text-slate-600">
                                    Ketidakhadiran: IZIN (HARI)
                                </td>
                                <td
                                    v-for="sem in semesters"
                                    :key="`izin-${sem}`"
                                    class="border border-slate-300 px-1 py-0.5 font-mono text-[10px] text-slate-700"
                                >
                                    {{ rekapMap[sem]?.izin_hari ?? 0 }}
                                </td>
                                <td class="border border-slate-300 px-1 py-0.5 text-[9px] text-slate-400">-</td>
                            </tr>

                            <!-- ABSENSI: TANPA KABAR -->
                            <tr class="bg-white">
                                <td colspan="2" class="border border-slate-300 px-3 py-0.5 text-left font-medium text-slate-600">
                                    Ketidakhadiran: TANPA KABAR (HARI)
                                </td>
                                <td
                                    v-for="sem in semesters"
                                    :key="`alpa-${sem}`"
                                    class="border border-slate-300 px-1 py-0.5 font-mono text-[10px] text-slate-700"
                                >
                                    {{ rekapMap[sem]?.tanpa_kabar_hari ?? 0 }}
                                </td>
                                <td class="border border-slate-300 px-1 py-0.5 text-[9px] text-slate-400">-</td>
                            </tr>

                            <!-- ABSENSI: JUMLAH -->
                            <tr class="bg-[#F8FAFC] font-semibold">
                                <td colspan="2" class="border border-slate-300 px-3 py-0.5 text-left font-bold text-[#0F3A5F]">
                                    JUMLAH KETIDAKHADIRAN (HARI)
                                </td>
                                <td
                                    v-for="sem in semesters"
                                    :key="`tot-absen-${sem}`"
                                    class="border border-slate-300 px-1 py-0.5 font-mono font-bold text-[10px] text-[#0F3A5F]"
                                >
                                    {{ rekapMap[sem]?.jumlah_absen_hari ?? 0 }}
                                </td>
                                <td class="border border-slate-300 px-1 py-0.5 text-[9px] text-slate-400">-</td>
                            </tr>

                            <!-- STATUS AKHIR TAHUN (Digabung per 2 semester: Sem I-II, III-IV, V-VI, VII-VIII, IX-X) -->
                            <tr class="bg-[#F1F5F9] font-bold border-t-2 border-slate-300">
                                <td colspan="2" class="border border-slate-300 px-3 py-1.5 text-left uppercase text-[#0F3A5F] font-bold">
                                    STATUS KENAIKAN / KELULUSAN
                                </td>
                                <!-- Span per 2 semester -->
                                <td colspan="2" class="border border-slate-300 px-1 py-1 text-[9.5px]">
                                    <div v-if="rekapMap[2]?.status_kenaikan === 'NAIK_TINGKAT'" class="text-[#0F3A5F] font-bold">
                                        NAIK KE {{ rekapMap[2]?.tingkat_tujuan || 'XI' }}
                                    </div>
                                    <div v-else-if="rekapMap[2]?.status_kenaikan === 'TIDAK_NAIK'" class="text-rose-600 font-bold">
                                        TIDAK NAIK
                                    </div>
                                    <div v-else class="text-slate-400">-</div>
                                </td>
                                <td colspan="2" class="border border-slate-300 px-1 py-1 text-[9.5px]">
                                    <div v-if="rekapMap[4]?.status_kenaikan === 'NAIK_TINGKAT'" class="text-[#0F3A5F] font-bold">
                                        NAIK KE {{ rekapMap[4]?.tingkat_tujuan || 'XII' }}
                                    </div>
                                    <div v-else-if="rekapMap[4]?.status_kenaikan === 'TIDAK_NAIK'" class="text-rose-600 font-bold">
                                        TIDAK NAIK
                                    </div>
                                    <div v-else class="text-slate-400">-</div>
                                </td>
                                <td colspan="2" class="border border-slate-300 px-1 py-1 text-[9.5px]">
                                    <div v-if="rekapMap[6]?.status_kenaikan === 'LULUS'" class="text-[#0F3A5F] font-extrabold">
                                        LULUS TAMAT
                                    </div>
                                    <div v-else-if="rekapMap[6]?.status_kenaikan === 'NAIK_TINGKAT'" class="text-[#0F3A5F] font-bold">
                                        NAIK KE {{ rekapMap[6]?.tingkat_tujuan || 'XIII' }}
                                    </div>
                                    <div v-else-if="rekapMap[6]?.status_kenaikan === 'TIDAK_NAIK'" class="text-rose-600 font-bold">
                                        TIDAK NAIK
                                    </div>
                                    <div v-else class="text-slate-400">-</div>
                                </td>
                                <td colspan="2" class="border border-slate-300 px-1 py-1 text-[9.5px]">
                                    <div v-if="rekapMap[8]?.status_kenaikan" class="font-bold text-[#0F3A5F]">
                                        {{ rekapMap[8]?.status_kenaikan }}
                                    </div>
                                    <div v-else class="text-slate-400">-</div>
                                </td>
                                <td colspan="2" class="border border-slate-300 px-1 py-1 text-[9.5px]">
                                    <div v-if="rekapMap[10]?.status_kenaikan" class="font-bold text-[#0F3A5F]">
                                        {{ rekapMap[10]?.status_kenaikan }}
                                    </div>
                                    <div v-else class="text-slate-400">-</div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-[9px] text-slate-400">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= MODAL INPUT / EDIT NILAI SEMESTER ================= -->
        <BaseModal
            :open="isNilaiModalOpen"
            :title="`Input Nilai & Prestasi Semester ${romanSemesters[selectedSemester - 1]} (${siswa.nama_lengkap})`"
            size="3xl"
            @close="closeNilaiModal"
        >
            <form id="nilaiForm" @submit.prevent="submitNilaiForm" class="space-y-5 text-xs">
                <!-- Semester & Tahun Selector -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-[#F8FAFC] p-4 rounded-xl border border-[#E2E8F0]">
                    <div>
                        <label class="block text-xs font-semibold text-[#0F3A5F] mb-1.5">Pilih Semester *</label>
                        <select
                            v-model.number="selectedSemester"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-[#E2E8F0] bg-white text-[#0F3A5F] font-semibold focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                            @change="onModalSemesterChange"
                        >
                            <option v-for="(rom, idx) in romanSemesters" :key="idx" :value="idx + 1">
                                Semester {{ rom }} (Semester {{ idx + 1 }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0F3A5F] mb-1.5">Tahun Pelajaran *</label>
                        <select
                            v-model="nilaiForm.tahun_pelajaran_id"
                            required
                            class="w-full px-3 py-2 text-xs rounded-lg border border-[#E2E8F0] bg-white text-[#0F3A5F] font-medium focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                            @change="onModalTahunChange"
                        >
                            <option value="" disabled>-- Pilih Tahun Pelajaran --</option>
                            <option v-for="t in tahunList" :key="t.id" :value="String(t.id)">
                                {{ t.tahun_pelajaran }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Nilai Mata Pelajaran -->
                <div>
                    <h4 class="font-semibold text-xs uppercase tracking-wider text-[#0F3A5F] border-b border-[#E2E8F0] pb-2 mb-3">
                        Nilai Angka Mata Pelajaran (Skala 0 - 100)
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        <div
                            v-for="(item, idx) in nilaiForm.nilai_akademik"
                            :key="item.mata_pelajaran_id"
                            class="p-2.5 rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] flex flex-col justify-between"
                        >
                            <label class="font-medium text-[#0F3A5F] text-[11px] block truncate mb-1">
                                {{ [...mapelUmum, ...mapelKejuruan].find(m => m.id === item.mata_pelajaran_id)?.nama_mapel }}
                            </label>
                            <input
                                v-model="item.nilai_angka"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                placeholder="85"
                                class="w-full px-2.5 py-1.5 border border-[#E2E8F0] rounded-md text-xs font-mono font-medium text-[#0F3A5F] bg-white focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                            />
                        </div>
                    </div>
                </div>

                <!-- PKL & Ekstrakurikuler -->
                <div>
                    <h4 class="font-semibold text-xs uppercase tracking-wider text-[#0F3A5F] border-b border-[#E2E8F0] pb-2 mb-3">
                        Praktik Kerja Lapangan (PKL) & Ekstrakurikuler
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- PKL -->
                        <div class="p-3 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl space-y-2">
                            <label class="font-semibold text-[#0F3A5F] block text-xs">Nilai Praktik Kerja Lapangan (PKL)</label>
                            <input
                                v-model="nilaiForm.nilai_pkl"
                                type="text"
                                placeholder="Contoh: 88 / Sangat Baik"
                                class="w-full px-3 py-1.5 border border-[#E2E8F0] rounded-md text-xs bg-white text-[#0F3A5F] focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                            />
                            <input
                                v-model="nilaiForm.keterangan_pkl"
                                type="text"
                                placeholder="Keterangan PKL (Perusahaan / Tempat)"
                                class="w-full px-3 py-1.5 border border-[#E2E8F0] rounded-md text-xs bg-white text-[#0F3A5F] focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                            />
                        </div>

                        <!-- Ekskul -->
                        <div class="p-3 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl space-y-2">
                            <label class="font-semibold text-[#0F3A5F] block text-xs">Kegiatan Ekstrakurikuler</label>
                            <div v-for="item in nilaiForm.nilai_ekskul" :key="item.ekstrakurikuler_id" class="flex items-center gap-2">
                                <span class="w-32 text-[11px] font-medium text-[#64748B] truncate">
                                    {{ ekskulList.find(e => e.id === item.ekstrakurikuler_id)?.nama_ekskul }}:
                                </span>
                                <input
                                    v-model="item.nilai_predikat"
                                    type="text"
                                    placeholder="A / B / C"
                                    class="flex-1 px-2.5 py-1 border border-[#E2E8F0] rounded-md text-xs bg-white text-[#0F3A5F] focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ranking & Absensi & Status Kenaikan -->
                <div>
                    <h4 class="font-semibold text-xs uppercase tracking-wider text-[#0F3A5F] border-b border-[#E2E8F0] pb-2 mb-3">
                        Ranking, Absensi (Hari) & Status Kenaikan
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-medium text-[#64748B] mb-1">Ranking Kelas</label>
                            <input
                                v-model="nilaiForm.ranking"
                                type="number"
                                min="1"
                                placeholder="1"
                                class="w-full px-3 py-1.5 border border-[#E2E8F0] rounded-md text-xs bg-white text-[#0F3A5F] focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-[#64748B] mb-1">Sakit (Hari)</label>
                            <input
                                v-model.number="nilaiForm.sakit_hari"
                                type="number"
                                min="0"
                                class="w-full px-3 py-1.5 border border-[#E2E8F0] rounded-md text-xs bg-white text-[#0F3A5F] focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-[#64748B] mb-1">Izin (Hari)</label>
                            <input
                                v-model.number="nilaiForm.izin_hari"
                                type="number"
                                min="0"
                                class="w-full px-3 py-1.5 border border-[#E2E8F0] rounded-md text-xs bg-white text-[#0F3A5F] focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-[#64748B] mb-1">Tanpa Kabar (Hari)</label>
                            <input
                                v-model.number="nilaiForm.tanpa_kabar_hari"
                                type="number"
                                min="0"
                                class="w-full px-3 py-1.5 border border-[#E2E8F0] rounded-md text-xs bg-white text-[#0F3A5F] focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                            />
                        </div>
                    </div>

                    <!-- Status Akhir Tahun -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 pt-3 border-t border-[#E2E8F0]">
                        <div>
                            <label class="block font-medium text-[#64748B] mb-1">Status Kenaikan / Kelulusan (Semester Genap)</label>
                            <select
                                v-model="nilaiForm.status_kenaikan"
                                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg text-xs bg-white text-[#0F3A5F] focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                            >
                                <option value="">-- Kosongkan (Bukan Akhir Tingkat) --</option>
                                <option value="NAIK_TINGKAT">NAIK TINGKAT</option>
                                <option value="TIDAK_NAIK">TIDAK NAIK</option>
                                <option value="LULUS">LULUS / TAMAT</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-[#64748B] mb-1">Tingkat Tujuan (Jika Naik Tingkat)</label>
                            <input
                                v-model="nilaiForm.tingkat_tujuan"
                                type="text"
                                placeholder="Contoh: XI TKJ / XII TKJ"
                                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg text-xs bg-white text-[#0F3A5F] focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2]"
                            />
                        </div>
                    </div>
                </div>
            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-2 w-full">
                    <BaseButton
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="closeNilaiModal"
                    >
                        Batal
                    </BaseButton>
                    <BaseButton
                        type="submit"
                        form="nilaiForm"
                        variant="primary"
                        size="sm"
                        :loading="nilaiForm.processing"
                    >
                        <Save class="w-4 h-4 mr-1.5" />
                        Simpan Nilai Semester {{ romanSemesters[selectedSemester - 1] }}
                    </BaseButton>
                </div>
            </template>
        </BaseModal>
    </div>
</template>

<style scoped>
/* Rotated vertical text for KETERANGAN header */
.vertical-text {
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    white-space: nowrap;
    text-align: center;
}

/* @media print for Paper F4 / Folio & A3 Landscape */
@media print {
    @page {
        size: 330mm 215mm landscape; /* F4 / Folio Landscape */
        margin: 6mm 8mm;
    }

    body {
        background-color: white !important;
        font-size: 9.5pt;
        color: black !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .buku-induk-canvas {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: none !important;
    }

    /* Keep side by side layout in print */
    .grid {
        display: flex !important;
        flex-direction: row !important;
        gap: 8px !important;
    }

    .panel-kiri {
        width: 32% !important;
        flex-shrink: 0 !important;
        border: 1.5px solid black !important;
    }

    .panel-kanan {
        width: 68% !important;
        flex-shrink: 0 !important;
        border: 1.5px solid black !important;
    }

    /* Retain borders in print */
    table, th, td {
        border-color: #334155 !important;
    }

    .bg-\[\#0F3A5F\] {
        background-color: #0F3A5F !important;
        color: white !important;
    }

    .bg-\[\#134571\] {
        background-color: #134571 !important;
        color: white !important;
    }

    .bg-\[\#F1F5F9\] {
        background-color: #f1f5f9 !important;
        color: #0F3A5F !important;
    }

    .bg-\[\#F8FAFC\] {
        background-color: #f8fafc !important;
    }
}
</style>
