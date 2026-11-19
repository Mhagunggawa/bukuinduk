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
    tahun_pelajaran_id: props.tahunList.find((t) => t.is_aktif)?.id
        ? String(props.tahunList.find((t) => t.is_aktif)?.id)
        : '',
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

const romanSemesters = [
    'I',
    'II',
    'III',
    'IV',
    'V',
    'VI',
    'VII',
    'VIII',
    'IX',
    'X',
];
</script>

<template>
    <Head title="Import Data Massal" />

    <div class="mx-auto max-w-5xl space-y-6">
        <!-- Header Section -->
        <div
            class="flex flex-col justify-between gap-4 rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs sm:flex-row sm:items-center"
        >
            <div>
                <h1
                    class="flex items-center gap-2 text-xl font-black tracking-tight text-blue-950 uppercase"
                >
                    <FileSpreadsheet class="h-6 w-6 text-blue-600" />
                    Import Massal & Sinkronisasi Data Siswa
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    Didesain khusus untuk menangani impor ratusan data siswa
                    (>600 siswa) secara cepat dan bebas timeout dengan metode
                    Chunking dan Batch Upsert.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-800"
                >
                    <CheckCircle2 class="h-3.5 w-3.5 text-blue-600" />
                    Anti-Timeout Chunk Reader
                </span>
            </div>
        </div>

        <!-- 3 Tab Navigasi (bg-blue-50 dengan tab aktif bg-blue-600 text-white) -->
        <div
            class="flex flex-wrap gap-1 rounded-xl border border-blue-100 bg-blue-50/70 p-1.5 sm:flex-nowrap"
        >
            <button
                type="button"
                :class="[
                    'flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg px-4 py-3 text-xs font-bold transition-all',
                    activeTab === 'identitas'
                        ? 'bg-blue-600 text-white shadow-md'
                        : 'text-blue-900 hover:bg-white/60',
                ]"
                @click="activeTab = 'identitas'"
            >
                <FileSpreadsheet class="h-4 w-4" />
                <span>(1) Import Identitas Siswa</span>
            </button>

            <button
                type="button"
                :class="[
                    'flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg px-4 py-3 text-xs font-bold transition-all',
                    activeTab === 'nilai'
                        ? 'bg-blue-600 text-white shadow-md'
                        : 'text-blue-900 hover:bg-white/60',
                ]"
                @click="activeTab = 'nilai'"
            >
                <FileText class="h-4 w-4" />
                <span>(2) Import Nilai & Absensi Semester</span>
            </button>

            <button
                type="button"
                :class="[
                    'flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg px-4 py-3 text-xs font-bold transition-all',
                    activeTab === 'foto'
                        ? 'bg-blue-600 text-white shadow-md'
                        : 'text-blue-900 hover:bg-white/60',
                ]"
                @click="activeTab = 'foto'"
            >
                <FolderArchive class="h-4 w-4" />
                <span>(3) Upload Massal Pasfoto (ZIP)</span>
            </button>
        </div>

        <!-- ================= TAB 1: IMPORT IDENTITAS SISWA ================= -->
        <div v-if="activeTab === 'identitas'" class="space-y-6">
            <!-- Info & Download Template Card -->
            <div
                class="flex flex-col justify-between gap-5 rounded-xl border border-slate-200 bg-white p-6 shadow-xs md:flex-row md:items-center"
            >
                <div class="max-w-xl space-y-1.5">
                    <div
                        class="inline-flex items-center gap-1.5 rounded bg-sky-50 px-2.5 py-1 text-xs font-bold text-blue-900"
                    >
                        <Info class="h-3.5 w-3.5 text-blue-600" />
                        <span>Langkah 1: Unduh Format Baku Excel</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">
                        Template Identitas & Biodata Siswa
                    </h3>
                    <p class="text-xs leading-relaxed text-slate-500">
                        File Excel berformat <strong>.xlsx</strong> yang telah
                        dilengkapi urutan kolom standar: NIS, NISN (sebagai
                        kunci unik upsert), Nama Lengkap, Kode Jurusan,
                        Tempat/Tgl Lahir, Data Orang Tua, Asal Sekolah, hingga
                        STTB.
                    </p>
                </div>

                <a
                    href="/import/template-identitas"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-900 px-5 py-3 text-xs font-bold text-white shadow-md transition-all hover:bg-blue-800 active:scale-95"
                >
                    <Download class="h-4 w-4" />
                    <span>Unduh Template Identitas Siswa (.xlsx)</span>
                </a>
            </div>

            <!-- Upload Dropzone Card -->
            <div
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs"
            >
                <h3
                    class="mb-4 flex items-center gap-2 text-sm font-bold tracking-wide text-blue-950 uppercase"
                >
                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                    Langkah 2: Unggah File Excel Identitas Siswa
                </h3>

                <form @submit.prevent="submitIdentitas" class="space-y-4">
                    <!-- Dropzone Area -->
                    <div
                        class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-blue-300 bg-blue-50/30 p-8 text-center transition-colors hover:border-blue-600 hover:bg-blue-50/70"
                        @click="identitasFileRef?.click()"
                    >
                        <input
                            ref="identitasFileRef"
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            class="hidden"
                            @change="handleIdentitasFile"
                        />
                        <div
                            class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 shadow-inner"
                        >
                            <UploadCloud class="h-7 w-7" />
                        </div>

                        <div v-if="identitasFileName" class="space-y-1">
                            <span
                                class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-900"
                            >
                                File Terpilih: {{ identitasFileName }}
                            </span>
                            <p class="text-[11px] text-slate-500">
                                Klik untuk mengganti file
                            </p>
                        </div>
                        <div v-else class="space-y-1">
                            <p class="text-sm font-bold text-slate-800">
                                Tarik & Letakkan file Excel di sini, atau
                                <span class="text-blue-600 underline"
                                    >Pilih File</span
                                >
                            </p>
                            <p class="text-xs text-slate-500">
                                Mendukung file .XLSX, .XLS, .CSV (Maksimal 20MB)
                            </p>
                        </div>
                    </div>

                    <!-- Submit Button & Progress Indicator -->
                    <div class="flex items-center justify-between pt-2">
                        <div class="text-xs text-slate-500">
                            * Sistem akan melakukan validasi NISN dan
                            meng-update otomatis jika NISN sudah ada.
                        </div>

                        <button
                            type="submit"
                            :disabled="
                                !identitasForm.file || identitasForm.processing
                            "
                            class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:bg-blue-700 active:scale-95 disabled:opacity-50"
                        >
                            <RefreshCw
                                v-if="identitasForm.processing"
                                class="h-4 w-4 animate-spin"
                            />
                            <UploadCloud v-else class="h-4 w-4" />
                            <span>{{
                                identitasForm.processing
                                    ? 'Memproses Import...'
                                    : 'Mulai Import Data Siswa'
                            }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= TAB 2: IMPORT NILAI & ABSENSI SEMESTER ================= -->
        <div v-if="activeTab === 'nilai'" class="space-y-6">
            <!-- Info & Download Template Card -->
            <div
                class="flex flex-col justify-between gap-5 rounded-xl border border-slate-200 bg-white p-6 shadow-xs md:flex-row md:items-center"
            >
                <div class="max-w-xl space-y-1.5">
                    <div
                        class="inline-flex items-center gap-1.5 rounded bg-sky-50 px-2.5 py-1 text-xs font-bold text-blue-900"
                    >
                        <Info class="h-3.5 w-3.5 text-blue-600" />
                        <span>Langkah 1: Unduh Format Baku Nilai Semester</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">
                        Template Nilai Akademik, Ekskul & Absensi
                    </h3>
                    <p class="text-xs leading-relaxed text-slate-500">
                        File Excel berisi kolom seluruh mata pelajaran (PAI,
                        PPKN, BINDO, PJOK, MTK, Kejuruan, PKL), ekskul (Pramuka,
                        Sispala, Olahraga), ranking, dan absensi
                        sakit/izin/alpa.
                    </p>
                </div>

                <a
                    href="/import/template-nilai"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-900 px-5 py-3 text-xs font-bold text-white shadow-md transition-all hover:bg-blue-800 active:scale-95"
                >
                    <Download class="h-4 w-4" />
                    <span>Unduh Template Nilai Semester (.xlsx)</span>
                </a>
            </div>

            <!-- Form Upload Nilai -->
            <div
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs"
            >
                <h3
                    class="mb-4 flex items-center gap-2 text-sm font-bold tracking-wide text-blue-950 uppercase"
                >
                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                    Langkah 2: Pilih Target Semester & Unggah Nilai
                </h3>

                <form @submit.prevent="submitNilai" class="space-y-5">
                    <!-- Dropdown Wajib: Tahun Pelajaran & Semester (I - X) -->
                    <div
                        class="grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2"
                    >
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-800"
                            >
                                Tahun Pelajaran *
                            </label>
                            <select
                                v-model="nilaiForm.tahun_pelajaran_id"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-800"
                            >
                                <option value="" disabled>
                                    -- Pilih Tahun Pelajaran --
                                </option>
                                <option
                                    v-for="t in tahunList"
                                    :key="t.id"
                                    :value="t.id"
                                >
                                    {{ t.tahun_pelajaran }}
                                    {{ t.is_aktif ? '(Aktif Saat Ini)' : '' }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-800"
                            >
                                Semester Target (I s.d. X) *
                            </label>
                            <select
                                v-model.number="nilaiForm.semester_ke"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-blue-900"
                            >
                                <option
                                    v-for="(rom, idx) in romanSemesters"
                                    :key="idx"
                                    :value="idx + 1"
                                >
                                    Semester {{ rom }} (Semester {{ idx + 1 }})
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Dropzone Area -->
                    <div
                        class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-indigo-300 bg-indigo-50/20 p-8 text-center transition-colors hover:border-indigo-600 hover:bg-indigo-50/50"
                        @click="nilaiFileRef?.click()"
                    >
                        <input
                            ref="nilaiFileRef"
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            class="hidden"
                            @change="handleNilaiFile"
                        />
                        <div
                            class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 shadow-inner"
                        >
                            <FileSpreadsheet class="h-7 w-7" />
                        </div>

                        <div v-if="nilaiFileName" class="space-y-1">
                            <span
                                class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-900"
                            >
                                File Terpilih: {{ nilaiFileName }}
                            </span>
                            <p class="text-[11px] text-slate-500">
                                Klik untuk mengganti file
                            </p>
                        </div>
                        <div v-else class="space-y-1">
                            <p class="text-sm font-bold text-slate-800">
                                Tarik & Letakkan file Nilai Excel di sini, atau
                                <span class="text-indigo-600 underline"
                                    >Pilih File</span
                                >
                            </p>
                            <p class="text-xs text-slate-500">
                                Jumlah Nilai & Nilai Rata-rata akan dihitung
                                otomatis oleh sistem
                            </p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-between pt-2">
                        <div class="text-xs text-slate-500">
                            * Siswa dicocokkan otomatis berdasarkan kolom NISN
                            di file Excel.
                        </div>

                        <button
                            type="submit"
                            :disabled="!nilaiForm.file || nilaiForm.processing"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:bg-blue-700 active:scale-95 disabled:opacity-50"
                        >
                            <RefreshCw
                                v-if="nilaiForm.processing"
                                class="h-4 w-4 animate-spin"
                            />
                            <UploadCloud v-else class="h-4 w-4" />
                            <span>{{
                                nilaiForm.processing
                                    ? 'Memproses Nilai...'
                                    : 'Mulai Import Nilai'
                            }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= TAB 3: UPLOAD MASSAL PASFOTO (ZIP) ================= -->
        <div v-if="activeTab === 'foto'" class="space-y-6">
            <!-- Panduan Format ZIP -->
            <div
                class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-xs"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                    >
                        <Camera class="h-6 w-6" />
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-extrabold text-slate-900">
                            Panduan Penamaan File Foto Siswa
                        </h3>
                        <p class="text-xs leading-relaxed text-slate-600">
                            Masukkan seluruh file foto siswa (format
                            <strong>JPG / PNG / WEBP</strong>) dengan ukuran
                            proporsional 3x4 cm ke dalam satu file berkas
                            kompresi <strong>.ZIP</strong>.
                        </p>
                    </div>
                </div>

                <!-- Rule Box -->
                <div
                    class="space-y-2 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900"
                >
                    <div class="flex items-center gap-1.5 font-bold">
                        <AlertTriangle
                            class="h-4 w-4 shrink-0 text-amber-600"
                        />
                        Ketentuan Penamaan File Foto:
                    </div>
                    <ul class="list-disc space-y-1 pl-5 text-[11.5px]">
                        <li>
                            Nama file foto
                            <strong>WAJIB menggunakan NISN siswa</strong>.
                            Contoh:
                            <code
                                class="rounded bg-amber-100 px-1 py-0.5 font-mono font-bold"
                                >0081234567.jpg</code
                            >
                            atau
                            <code
                                class="rounded bg-amber-100 px-1 py-0.5 font-mono font-bold"
                                >0081234568.png</code
                            >
                        </li>
                        <li>
                            Foto akan langsung dipasangkan ke bingkai PasPhoto
                            3x4 pada lembar Buku Induk siswa yang bersangkutan.
                        </li>
                        <li>Ukuran maksimal berkas ZIP adalah 50MB.</li>
                    </ul>
                </div>
            </div>

            <!-- Dropzone ZIP -->
            <div
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs"
            >
                <form @submit.prevent="submitFoto" class="space-y-4">
                    <div
                        class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-emerald-300 bg-emerald-50/20 p-8 text-center transition-colors hover:border-emerald-600 hover:bg-emerald-50/50"
                        @click="fotoFileRef?.click()"
                    >
                        <input
                            ref="fotoFileRef"
                            type="file"
                            accept=".zip"
                            class="hidden"
                            @change="handleFotoFile"
                        />
                        <div
                            class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 shadow-inner"
                        >
                            <FolderArchive class="h-7 w-7" />
                        </div>

                        <div v-if="fotoFileName" class="space-y-1">
                            <span
                                class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-900"
                            >
                                File Terpilih: {{ fotoFileName }}
                            </span>
                            <p class="text-[11px] text-slate-500">
                                Klik untuk mengganti berkas ZIP
                            </p>
                        </div>
                        <div v-else class="space-y-1">
                            <p class="text-sm font-bold text-slate-800">
                                Tarik & Letakkan file arsip
                                <span class="font-extrabold text-emerald-700"
                                    >.ZIP</span
                                >
                                di sini, atau
                                <span class="text-emerald-600 underline"
                                    >Pilih File</span
                                >
                            </p>
                            <p class="text-xs text-slate-500">
                                Hanya format file .ZIP yang diperbolehkan
                                (Maksimal 50MB)
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs text-slate-500">
                            * File tidak cocok akan dilaporkan pada tabel log di
                            bawah.
                        </span>

                        <button
                            type="submit"
                            :disabled="
                                !fotoForm.zip_file || fotoForm.processing
                            "
                            class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:bg-blue-700 active:scale-95 disabled:opacity-50"
                        >
                            <RefreshCw
                                v-if="fotoForm.processing"
                                class="h-4 w-4 animate-spin"
                            />
                            <UploadCloud v-else class="h-4 w-4" />
                            <span>{{
                                fotoForm.processing
                                    ? 'Mengekstrak Foto...'
                                    : 'Unggah & Pasangkan Foto'
                            }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= LOG VALIDASI BARIS ERROR ================= -->
        <div
            v-if="importErrors.length > 0"
            class="space-y-3 rounded-xl border border-rose-200 bg-white p-5 shadow-xs"
        >
            <div
                class="flex items-center justify-between border-b border-rose-100 pb-3"
            >
                <div
                    class="flex items-center gap-2 text-sm font-bold text-rose-700"
                >
                    <AlertTriangle class="h-5 w-5 text-rose-600" />
                    <span
                        >Log Hasil Validasi: Terdapat
                        {{ importErrors.length }} Baris / File Yang Gagal</span
                    >
                </div>
            </div>

            <div class="max-h-72 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-rose-50 text-[10px] font-bold text-rose-900 uppercase"
                    >
                        <tr>
                            <th class="w-20 px-3 py-2">Baris / File</th>
                            <th class="w-36 px-3 py-2">NISN</th>
                            <th class="px-3 py-2">
                                Keterangan Kesalahan / Penyebab
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rose-100 font-sans">
                        <tr
                            v-for="(err, idx) in importErrors"
                            :key="idx"
                            class="hover:bg-rose-50/50"
                        >
                            <td
                                class="px-3 py-2 font-mono font-bold text-slate-800"
                            >
                                {{
                                    err.row
                                        ? `Baris ${err.row}`
                                        : err.file || '-'
                                }}
                            </td>
                            <td
                                class="px-3 py-2 font-mono font-semibold text-rose-800"
                            >
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
