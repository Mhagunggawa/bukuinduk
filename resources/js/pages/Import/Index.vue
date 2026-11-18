<script setup lang="ts">
import { computed, ref } from 'vue';
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
    ArrowRight,
    Camera,
    RefreshCw,
} from '@lucide/vue';

interface TahunPelajaran {
    id: number;
    tahun_pelajaran: string;
    is_aktif: boolean;
}

interface Jurusan {
    id: number;
    nama_konsentrasi: string;
    kode_singkat: string;
}

const props = defineProps<{
    tahunList: TahunPelajaran[];
    jurusanList: Jurusan[];
}>();

const page = usePage();
const flash = computed(() => (page.props as any).flash || {});
const importErrors = computed(() => flash.value.import_errors || []);

// Active Tab (1: Identitas, 2: Nilai, 3: Foto ZIP)
const activeTab = ref<'identitas' | 'nilai' | 'foto'>('identitas');

// Tab 1: Form Identitas
const identitasForm = useForm({
    file: null as File | null,
});
const identitasFileRef = ref<HTMLInputElement | null>(null);
const identitasFileName = ref<string>('');

const handleIdentitasFile = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        identitasForm.file = input.files[0];
        identitasFileName.value = input.files[0].name;
    }
};

const submitIdentitas = () => {
    if (!identitasForm.file) return;
    identitasForm.post('/import/identitas', {
        onSuccess: () => {
            identitasForm.reset();
            identitasFileName.value = '';
            if (identitasFileRef.value) identitasFileRef.value.value = '';
        },
    });
};

// Tab 2: Form Nilai Semester
const nilaiForm = useForm({
    file: null as File | null,
    tahun_pelajaran_id: props.tahunList.find((t) => t.is_aktif)?.id ? String(props.tahunList.find((t) => t.is_aktif)?.id) : '',
    semester_ke: 1,
});
const nilaiFileRef = ref<HTMLInputElement | null>(null);
const nilaiFileName = ref<string>('');

const handleNilaiFile = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        nilaiForm.file = input.files[0];
        nilaiFileName.value = input.files[0].name;
    }
};

const submitNilai = () => {
    if (!nilaiForm.file) return;
    nilaiForm.post('/import/nilai', {
        onSuccess: () => {
            nilaiForm.file = null;
            nilaiFileName.value = '';
            if (nilaiFileRef.value) nilaiFileRef.value.value = '';
        },
    });
};

// Tab 3: Form Upload ZIP Pasfoto
const fotoForm = useForm({
    zip_file: null as File | null,
});
const fotoFileRef = ref<HTMLInputElement | null>(null);
const fotoFileName = ref<string>('');

const handleFotoFile = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        fotoForm.zip_file = input.files[0];
        fotoFileName.value = input.files[0].name;
    }
};

const submitFoto = () => {
    if (!fotoForm.zip_file) return;
    fotoForm.post('/import/zip-foto', {
        onSuccess: () => {
            fotoForm.reset();
            fotoFileName.value = '';
            if (fotoFileRef.value) fotoFileRef.value.value = '';
        },
    });
};

const romanSemesters = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
</script>

<template>
    <Head title="Import Data Massal" />

        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Header Section -->
            <div class="bg-white p-6 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-blue-950 uppercase tracking-tight flex items-center gap-2">
                        <FileSpreadsheet class="w-6 h-6 text-blue-600" />
                        Import Massal & Sinkronisasi Data Siswa
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Didesain khusus untuk menangani impor ratusan data siswa (>600 siswa) secara cepat dan bebas timeout dengan metode Chunking dan Batch Upsert.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-blue-800 text-xs font-bold">
                        <CheckCircle2 class="w-3.5 h-3.5 text-blue-600" />
                        Anti-Timeout Chunk Reader
                    </span>
                </div>
            </div>

            <!-- 3 Tab Navigasi (bg-blue-50 dengan tab aktif bg-blue-600 text-white) -->
            <div class="bg-blue-50/70 p-1.5 rounded-xl border border-blue-100 flex flex-wrap sm:flex-nowrap gap-1">
                <button
                    type="button"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-lg text-xs font-bold transition-all cursor-pointer',
                        activeTab === 'identitas'
                            ? 'bg-blue-600 text-white shadow-md'
                            : 'text-blue-900 hover:bg-white/60',
                    ]"
                    @click="activeTab = 'identitas'"
                >
                    <FileSpreadsheet class="w-4 h-4" />
                    <span>(1) Import Identitas Siswa</span>
                </button>

                <button
                    type="button"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-lg text-xs font-bold transition-all cursor-pointer',
                        activeTab === 'nilai'
                            ? 'bg-blue-600 text-white shadow-md'
                            : 'text-blue-900 hover:bg-white/60',
                    ]"
                    @click="activeTab = 'nilai'"
                >
                    <FileText class="w-4 h-4" />
                    <span>(2) Import Nilai & Absensi Semester</span>
                </button>

                <button
                    type="button"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-lg text-xs font-bold transition-all cursor-pointer',
                        activeTab === 'foto'
                            ? 'bg-blue-600 text-white shadow-md'
                            : 'text-blue-900 hover:bg-white/60',
                    ]"
                    @click="activeTab = 'foto'"
                >
                    <FolderArchive class="w-4 h-4" />
                    <span>(3) Upload Massal Pasfoto (ZIP)</span>
                </button>
            </div>

            <!-- ================= TAB 1: IMPORT IDENTITAS SISWA ================= -->
            <div v-if="activeTab === 'identitas'" class="space-y-6">
                <!-- Info & Download Template Card -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-1.5 max-w-xl">
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-900 bg-sky-50 px-2.5 py-1 rounded">
                            <Info class="w-3.5 h-3.5 text-blue-600" />
                            <span>Langkah 1: Unduh Format Baku Excel</span>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            Template Identitas & Biodata Siswa
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            File Excel berformat <strong>.xlsx</strong> yang telah dilengkapi urutan kolom standar: NIS, NISN (sebagai kunci unik upsert), Nama Lengkap, Kode Jurusan, Tempat/Tgl Lahir, Data Orang Tua, Asal Sekolah, hingga STTB.
                        </p>
                    </div>

                    <a
                        href="/import/template-identitas"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-blue-900 hover:bg-blue-800 text-white font-bold text-xs shadow-md transition-all active:scale-95 shrink-0"
                    >
                        <Download class="w-4 h-4" />
                        <span>Unduh Template Identitas Siswa (.xlsx)</span>
                    </a>
                </div>

                <!-- Upload Dropzone Card -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-blue-950 uppercase tracking-wide mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        Langkah 2: Unggah File Excel Identitas Siswa
                    </h3>

                    <form @submit.prevent="submitIdentitas" class="space-y-4">
                        <!-- Dropzone Area -->
                        <div
                            class="border-2 border-dashed border-blue-300 hover:border-blue-600 bg-blue-50/30 hover:bg-blue-50/70 rounded-2xl p-8 text-center transition-colors cursor-pointer flex flex-col items-center justify-center"
                            @click="identitasFileRef?.click()"
                        >
                            <input
                                ref="identitasFileRef"
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                class="hidden"
                                @change="handleIdentitasFile"
                            />
                            <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mb-3 shadow-inner">
                                <UploadCloud class="w-7 h-7" />
                            </div>

                            <div v-if="identitasFileName" class="space-y-1">
                                <span class="text-xs font-bold text-blue-900 bg-blue-100 px-3 py-1 rounded-full">
                                    File Terpilih: {{ identitasFileName }}
                                </span>
                                <p class="text-[11px] text-slate-500">Klik untuk mengganti file</p>
                            </div>
                            <div v-else class="space-y-1">
                                <p class="text-sm font-bold text-slate-800">
                                    Tarik & Letakkan file Excel di sini, atau <span class="text-blue-600 underline">Pilih File</span>
                                </p>
                                <p class="text-xs text-slate-500">
                                    Mendukung file .XLSX, .XLS, .CSV (Maksimal 20MB)
                                </p>
                            </div>
                        </div>

                        <!-- Submit Button & Progress Indicator -->
                        <div class="flex items-center justify-between pt-2">
                            <div class="text-xs text-slate-500">
                                * Sistem akan melakukan validasi NISN dan meng-update otomatis jika NISN sudah ada.
                            </div>

                            <button
                                type="submit"
                                :disabled="!identitasForm.file || identitasForm.processing"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md active:scale-95 disabled:opacity-50 cursor-pointer"
                            >
                                <RefreshCw v-if="identitasForm.processing" class="w-4 h-4 animate-spin" />
                                <UploadCloud v-else class="w-4 h-4" />
                                <span>{{ identitasForm.processing ? 'Memproses Import...' : 'Mulai Import Data Siswa' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ================= TAB 2: IMPORT NILAI & ABSENSI SEMESTER ================= -->
            <div v-if="activeTab === 'nilai'" class="space-y-6">
                <!-- Info & Download Template Card -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-1.5 max-w-xl">
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-900 bg-sky-50 px-2.5 py-1 rounded">
                            <Info class="w-3.5 h-3.5 text-blue-600" />
                            <span>Langkah 1: Unduh Format Baku Nilai Semester</span>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            Template Nilai Akademik, Ekskul & Absensi
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            File Excel berisi kolom seluruh mata pelajaran (PAI, PPKN, BINDO, PJOK, MTK, Kejuruan, PKL), ekskul (Pramuka, Sispala, Olahraga), ranking, dan absensi sakit/izin/alpa.
                        </p>
                    </div>

                    <a
                        href="/import/template-nilai"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-blue-900 hover:bg-blue-800 text-white font-bold text-xs shadow-md transition-all active:scale-95 shrink-0"
                    >
                        <Download class="w-4 h-4" />
                        <span>Unduh Template Nilai Semester (.xlsx)</span>
                    </a>
                </div>

                <!-- Form Upload Nilai -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-blue-950 uppercase tracking-wide mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        Langkah 2: Pilih Target Semester & Unggah Nilai
                    </h3>

                    <form @submit.prevent="submitNilai" class="space-y-5">
                        <!-- Dropdown Wajib: Tahun Pelajaran & Semester (I - X) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <div>
                                <label class="block font-bold text-slate-800 text-xs mb-1.5">
                                    Tahun Pelajaran *
                                </label>
                                <select
                                    v-model="nilaiForm.tahun_pelajaran_id"
                                    required
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold text-slate-800 bg-white"
                                >
                                    <option value="" disabled>-- Pilih Tahun Pelajaran --</option>
                                    <option v-for="t in tahunList" :key="t.id" :value="t.id">
                                        {{ t.tahun_pelajaran }} {{ t.is_aktif ? '(Aktif Saat Ini)' : '' }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-800 text-xs mb-1.5">
                                    Semester Target (I s.d. X) *
                                </label>
                                <select
                                    v-model.number="nilaiForm.semester_ke"
                                    required
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold text-blue-900 bg-white"
                                >
                                    <option v-for="(rom, idx) in romanSemesters" :key="idx" :value="idx + 1">
                                        Semester {{ rom }} (Semester {{ idx + 1 }})
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Dropzone Area -->
                        <div
                            class="border-2 border-dashed border-indigo-300 hover:border-indigo-600 bg-indigo-50/20 hover:bg-indigo-50/50 rounded-2xl p-8 text-center transition-colors cursor-pointer flex flex-col items-center justify-center"
                            @click="nilaiFileRef?.click()"
                        >
                            <input
                                ref="nilaiFileRef"
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                class="hidden"
                                @change="handleNilaiFile"
                            />
                            <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-3 shadow-inner">
                                <FileSpreadsheet class="w-7 h-7" />
                            </div>

                            <div v-if="nilaiFileName" class="space-y-1">
                                <span class="text-xs font-bold text-indigo-900 bg-indigo-100 px-3 py-1 rounded-full">
                                    File Terpilih: {{ nilaiFileName }}
                                </span>
                                <p class="text-[11px] text-slate-500">Klik untuk mengganti file</p>
                            </div>
                            <div v-else class="space-y-1">
                                <p class="text-sm font-bold text-slate-800">
                                    Tarik & Letakkan file Nilai Excel di sini, atau <span class="text-indigo-600 underline">Pilih File</span>
                                </p>
                                <p class="text-xs text-slate-500">
                                    Jumlah Nilai & Nilai Rata-rata akan dihitung otomatis oleh sistem
                                </p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between pt-2">
                            <div class="text-xs text-slate-500">
                                * Siswa dicocokkan otomatis berdasarkan kolom NISN di file Excel.
                            </div>

                            <button
                                type="submit"
                                :disabled="!nilaiForm.file || nilaiForm.processing"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md active:scale-95 disabled:opacity-50 cursor-pointer"
                            >
                                <RefreshCw v-if="nilaiForm.processing" class="w-4 h-4 animate-spin" />
                                <UploadCloud v-else class="w-4 h-4" />
                                <span>{{ nilaiForm.processing ? 'Memproses Nilai...' : 'Mulai Import Nilai' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ================= TAB 3: UPLOAD MASSAL PASFOTO (ZIP) ================= -->
            <div v-if="activeTab === 'foto'" class="space-y-6">
                <!-- Panduan Format ZIP -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <Camera class="w-6 h-6" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-base font-extrabold text-slate-900">
                                Panduan Penamaan File Foto Siswa
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Masukkan seluruh file foto siswa (format <strong>JPG / PNG / WEBP</strong>) dengan ukuran proporsional 3x4 cm ke dalam satu file berkas kompresi <strong>.ZIP</strong>.
                            </p>
                        </div>
                    </div>

                    <!-- Rule Box -->
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-2">
                        <div class="font-bold flex items-center gap-1.5">
                            <AlertTriangle class="w-4 h-4 text-amber-600 shrink-0" />
                            Ketentuan Penamaan File Foto:
                        </div>
                        <ul class="list-disc pl-5 space-y-1 text-[11.5px]">
                            <li>Nama file foto <strong>WAJIB menggunakan NISN siswa</strong>. Contoh: <code class="bg-amber-100 px-1 py-0.5 rounded font-mono font-bold">0081234567.jpg</code> atau <code class="bg-amber-100 px-1 py-0.5 rounded font-mono font-bold">0081234568.png</code></li>
                            <li>Foto akan langsung dipasangkan ke bingkai PasPhoto 3x4 pada lembar Buku Induk siswa yang bersangkutan.</li>
                            <li>Ukuran maksimal berkas ZIP adalah 50MB.</li>
                        </ul>
                    </div>
                </div>

                <!-- Dropzone ZIP -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
                    <form @submit.prevent="submitFoto" class="space-y-4">
                        <div
                            class="border-2 border-dashed border-emerald-300 hover:border-emerald-600 bg-emerald-50/20 hover:bg-emerald-50/50 rounded-2xl p-8 text-center transition-colors cursor-pointer flex flex-col items-center justify-center"
                            @click="fotoFileRef?.click()"
                        >
                            <input
                                ref="fotoFileRef"
                                type="file"
                                accept=".zip"
                                class="hidden"
                                @change="handleFotoFile"
                            />
                            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3 shadow-inner">
                                <FolderArchive class="w-7 h-7" />
                            </div>

                            <div v-if="fotoFileName" class="space-y-1">
                                <span class="text-xs font-bold text-emerald-900 bg-emerald-100 px-3 py-1 rounded-full">
                                    File Terpilih: {{ fotoFileName }}
                                </span>
                                <p class="text-[11px] text-slate-500">Klik untuk mengganti berkas ZIP</p>
                            </div>
                            <div v-else class="space-y-1">
                                <p class="text-sm font-bold text-slate-800">
                                    Tarik & Letakkan file arsip <span class="text-emerald-700 font-extrabold">.ZIP</span> di sini, atau <span class="text-emerald-600 underline">Pilih File</span>
                                </p>
                                <p class="text-xs text-slate-500">
                                    Hanya format file .ZIP yang diperbolehkan (Maksimal 50MB)
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <span class="text-xs text-slate-500">
                                * File tidak cocok akan dilaporkan pada tabel log di bawah.
                            </span>

                            <button
                                type="submit"
                                :disabled="!fotoForm.zip_file || fotoForm.processing"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md active:scale-95 disabled:opacity-50 cursor-pointer"
                            >
                                <RefreshCw v-if="fotoForm.processing" class="w-4 h-4 animate-spin" />
                                <UploadCloud v-else class="w-4 h-4" />
                                <span>{{ fotoForm.processing ? 'Mengekstrak Foto...' : 'Unggah & Pasangkan Foto' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ================= LOG VALIDASI BARIS ERROR ================= -->
            <div
                v-if="importErrors.length > 0"
                class="bg-white rounded-xl border border-rose-200 p-5 shadow-xs space-y-3"
            >
                <div class="flex items-center justify-between border-b border-rose-100 pb-3">
                    <div class="flex items-center gap-2 text-rose-700 font-bold text-sm">
                        <AlertTriangle class="w-5 h-5 text-rose-600" />
                        <span>Log Hasil Validasi: Terdapat {{ importErrors.length }} Baris / File Yang Gagal</span>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-72">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-rose-50 text-rose-900 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="px-3 py-2 w-20">Baris / File</th>
                                <th class="px-3 py-2 w-36">NISN</th>
                                <th class="px-3 py-2">Keterangan Kesalahan / Penyebab</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-100 font-sans">
                            <tr
                                v-for="(err, idx) in importErrors"
                                :key="idx"
                                class="hover:bg-rose-50/50"
                            >
                                <td class="px-3 py-2 font-mono font-bold text-slate-800">
                                    {{ err.row ? `Baris ${err.row}` : (err.file || '-') }}
                                </td>
                                <td class="px-3 py-2 font-mono font-semibold text-rose-800">
                                    {{ err.nisn || '-' }}
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ err.message }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</template>
