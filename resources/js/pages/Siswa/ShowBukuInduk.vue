<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Printer,
    Edit3,
    BookOpen,
    Save,
    X,
    UserCheck,
    CheckCircle2,
    Calendar,
    GraduationCap,
} from '@lucide/vue';

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
    is_aktif: boolean;
}

interface NilaiAkademikItem {
    id: number;
    mata_pelajaran_id: number;
    semester_ke: number;
    nilai_angka: number | null;
    keterangan: string | null;
    mata_pelajaran?: Mapel;
}

interface NilaiEkskulItem {
    id: number;
    ekstrakurikuler_id: number;
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

// Print Action
const handlePrint = () => {
    window.print();
};

// 10 Semesters list
const semesters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
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

// Quick Lookup Maps for matrix rendering
const academicMap = computed(() => {
    const map: Record<string, number | null> = {};
    for (const item of props.siswa.nilai_akademik || []) {
        map[`${item.mata_pelajaran_id}_${item.semester_ke}`] = item.nilai_angka;
    }
    return map;
});

const ekskulMap = computed(() => {
    const map: Record<string, string | null> = {};
    for (const item of props.siswa.nilai_ekstrakurikuler || []) {
        map[`${item.ekstrakurikuler_id}_${item.semester_ke}`] =
            item.nilai_predikat;
    }
    return map;
});

const rekapMap = computed(() => {
    const map: Record<number, RekapSemesterItem> = {};
    for (const item of props.siswa.rekap_semester || []) {
        map[item.semester_ke] = item;
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

// Determine Year for Column Header
const getTahunPelajaranForSemester = (sem: number) => {
    const r = rekapMap.value[sem];
    if (r?.tahun_pelajaran?.tahun_pelajaran) {
        return r.tahun_pelajaran.tahun_pelajaran;
    }
    // Estimate based on start year if available
    const diterima = props.siswa.diterima_tanggal;
    let baseYear = 2025;
    if (diterima) {
        const parsed = parseInt(diterima.substring(0, 4), 10);
        if (!isNaN(parsed)) baseYear = parsed;
    }
    const yearOffset = Math.floor((sem - 1) / 2);
    return `${baseYear + yearOffset}/${baseYear + yearOffset + 1}`;
};

// Modal Input/Edit Nilai Semester
const isNilaiModalOpen = ref(false);
const selectedSemester = ref(1);

const nilaiForm = useForm({
    semester_ke: 1,
    tahun_pelajaran_id: '',
    nilai_akademik: [] as {
        mata_pelajaran_id: number;
        nilai_angka: string | number;
        keterangan: string;
    }[],
    nilai_ekskul: [] as {
        ekstrakurikuler_id: number;
        nilai_predikat: string;
        keterangan: string;
    }[],
    nilai_pkl: '',
    keterangan_pkl: '',
    ranking: '',
    sakit_hari: 0,
    izin_hari: 0,
    tanpa_kabar_hari: 0,
    status_kenaikan: '',
    tingkat_tujuan: '',
});

const openNilaiModal = (sem: number = 1) => {
    selectedSemester.value = sem;
    nilaiForm.semester_ke = sem;

    // Load existing rekap
    const currentRekap = rekapMap.value[sem];
    nilaiForm.tahun_pelajaran_id = currentRekap?.tahun_pelajaran_id
        ? String(currentRekap.tahun_pelajaran_id)
        : props.tahunList.find((t) => t.is_aktif)?.id
          ? String(props.tahunList.find((t) => t.is_aktif)?.id)
          : '';
    nilaiForm.nilai_pkl = currentRekap?.nilai_pkl || '';
    nilaiForm.keterangan_pkl = currentRekap?.keterangan_pkl || '';
    nilaiForm.ranking = currentRekap?.ranking
        ? String(currentRekap.ranking)
        : '';
    nilaiForm.sakit_hari = currentRekap?.sakit_hari ?? 0;
    nilaiForm.izin_hari = currentRekap?.izin_hari ?? 0;
    nilaiForm.tanpa_kabar_hari = currentRekap?.tanpa_kabar_hari ?? 0;
    nilaiForm.status_kenaikan = currentRekap?.status_kenaikan || '';
    nilaiForm.tingkat_tujuan = currentRekap?.tingkat_tujuan || '';

    // Academic mapel
    const allMapel = [...props.mapelUmum, ...props.mapelKejuruan];
    nilaiForm.nilai_akademik = allMapel.map((m) => {
        const val = academicMap.value[`${m.id}_${sem}`];
        return {
            mata_pelajaran_id: m.id,
            nilai_angka: val !== null && val !== undefined ? String(val) : '',
            keterangan: 'Tuntas',
        };
    });

    // Ekskul
    nilaiForm.nilai_ekskul = props.ekskulList.map((e) => {
        const val = ekskulMap.value[`${e.id}_${sem}`];
        return {
            ekstrakurikuler_id: e.id,
            nilai_predikat: val || '',
            keterangan: '',
        };
    });

    isNilaiModalOpen.value = true;
};

const closeNilaiModal = () => {
    isNilaiModalOpen.value = false;
};

const submitNilaiForm = () => {
    nilaiForm.post(`/siswa/${props.siswa.id}/nilai-semester`, {
        onSuccess: () => {
            closeNilaiModal();
        },
    });
};
</script>

<template>
    <Head :title="`Buku Induk - ${siswa.nama_lengkap}`" />
    <!-- Action Bar Atas Kanvas (Disembunyikan saat Print) -->
    <div
        class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs print:hidden"
    >
        <div class="flex items-center gap-3">
            <Link
                href="/siswa"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 transition-colors hover:bg-slate-50"
            >
                <ArrowLeft class="h-4 w-4" />
                <span>Kembali ke Direktori</span>
            </Link>

            <div class="hidden h-5 w-px bg-slate-200 sm:block"></div>

            <div class="hidden sm:block">
                <span class="text-xs font-medium text-slate-500"
                    >Buku Induk Siswa:</span
                >
                <span class="ml-1.5 text-xs font-bold text-slate-900"
                    >{{ siswa.nama_lengkap }} ({{ siswa.nis }})</span
                >
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Dropdown Pilih Semester untuk input nilai -->
            <button
                type="button"
                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-3.5 py-2 text-xs font-bold text-indigo-900 transition-colors hover:bg-indigo-100"
                @click="openNilaiModal(1)"
            >
                <Edit3 class="h-3.5 w-3.5 text-indigo-700" />
                <span>Input / Edit Nilai Semester</span>
            </button>

            <!-- Tombol Cetak Buku Induk (PDF / Print) -->
            <button
                type="button"
                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-md transition-all hover:bg-blue-700 active:scale-95"
                @click="handlePrint"
            >
                <Printer class="h-4 w-4" />
                <span>Cetak Buku Induk (F4/A3)</span>
            </button>
        </div>
    </div>

    <!-- Kanvas Fisik Buku Induk (Dioptimalkan untuk Layar & @media print F4/A3 Landscape) -->
    <div
        class="buku-induk-canvas mx-auto overflow-x-auto rounded-lg border border-slate-300 bg-white p-6 text-slate-900 shadow-md"
    >
        <!-- Header Lembar Buku Induk -->
        <div
            class="mb-4 flex flex-col justify-between gap-3 border-b-4 border-slate-900 pb-3 sm:flex-row sm:items-end"
        >
            <div class="flex-1">
                <h1
                    class="text-xl leading-tight font-black tracking-wider text-slate-950 uppercase sm:text-2xl"
                >
                    HASIL PRESTASI PENILAIAN
                </h1>
                <div
                    class="mt-0.5 text-[11px] font-bold tracking-widest text-slate-600 uppercase"
                >
                    BUKU INDUK SISWA SEKOLAH MENENGAH KEJURUAN (SMK)
                </div>
            </div>

            <!-- Info Jurusan Header -->
            <div
                class="space-y-0.5 text-right text-[11px] font-semibold text-slate-800"
            >
                <div>
                    <span class="text-slate-500 uppercase"
                        >BIDANG STUDI KEAHLIAN:</span
                    >
                    <span class="ml-1 font-bold text-slate-900 uppercase">
                        {{
                            siswa.konsentrasi_keahlian?.program_keahlian
                                ?.bidang_studi?.nama_bidang_studi ||
                            'TEKNOLOGI INFORMASI'
                        }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase"
                        >PROGRAM KEAHLIAN:</span
                    >
                    <span class="ml-1 font-bold text-slate-900 uppercase">
                        {{
                            siswa.konsentrasi_keahlian?.program_keahlian
                                ?.nama_program_keahlian ||
                            'TEKNIK JARINGAN KOMPUTER & TELEKOMUNIKASI'
                        }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-500 uppercase"
                        >KONSENTRASI KEAHLIAN:</span
                    >
                    <span class="ml-1 font-black text-blue-900 uppercase">
                        {{
                            siswa.konsentrasi_keahlian?.nama_konsentrasi ||
                            'TEKNIK KOMPUTER DAN JARINGAN (TKJ)'
                        }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Konten Utama: 2 Panel Berdampingan (Kiri 32%, Kanan 68%) -->
        <div class="grid grid-cols-12 items-start gap-4">
            <!-- ================= PANEL KIRI (32% - IDENTITAS SISWA) ================= -->
            <div
                class="panel-kiri col-span-12 space-y-3 rounded border-2 border-slate-900 bg-slate-50/40 p-3 font-sans text-[11px] lg:col-span-4"
            >
                <div
                    class="rounded-xs bg-blue-900 py-1 text-center text-[11px] font-black tracking-wider text-white uppercase"
                >
                    I. IDENTITAS SISWA
                </div>

                <!-- Biodata Siswa 1-9 -->
                <table class="w-full border-collapse text-left leading-tight">
                    <tbody>
                        <tr class="border-b border-slate-200">
                            <td class="w-32 py-1 font-bold text-slate-700">
                                1. NIS
                            </td>
                            <td class="w-3 text-center">:</td>
                            <td class="py-1 font-mono font-bold text-slate-900">
                                {{ siswa.nis }}
                            </td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-1 font-bold text-slate-700">
                                2. NISN
                            </td>
                            <td class="text-center">:</td>
                            <td class="py-1 font-mono font-bold text-slate-900">
                                {{ siswa.nisn }}
                            </td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-1 font-bold text-slate-700">
                                3. Jenis Kelamin
                            </td>
                            <td class="text-center">:</td>
                            <td class="py-1 font-bold text-slate-900">
                                {{
                                    siswa.jenis_kelamin === 'L'
                                        ? 'LAKI-LAKI'
                                        : 'PEREMPUAN'
                                }}
                            </td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-1 font-bold text-slate-700">
                                4. Nama Lengkap
                            </td>
                            <td class="text-center">:</td>
                            <td
                                class="py-1 font-black text-slate-950 uppercase"
                            >
                                {{ siswa.nama_lengkap }}
                            </td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-1 font-bold text-slate-700">
                                5. Kelas Saat Ini
                            </td>
                            <td class="text-center">:</td>
                            <td class="py-1 font-bold text-blue-900">
                                {{ siswa.kelas_saat_ini || '-' }}
                            </td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-1 font-bold text-slate-700">
                                6. Tempat, Tgl Lahir
                            </td>
                            <td class="text-center">:</td>
                            <td class="py-1 text-slate-900">
                                {{ siswa.tempat_lahir || '-' }},
                                {{ formatDate(siswa.tanggal_lahir) }}
                            </td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-1 font-bold text-slate-700">
                                7. Warga Negara
                            </td>
                            <td class="text-center">:</td>
                            <td class="py-1 text-slate-900">
                                {{ siswa.warga_negara || 'WNI' }}
                            </td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-1 font-bold text-slate-700">
                                8. Agama
                            </td>
                            <td class="text-center">:</td>
                            <td class="py-1 text-slate-900">
                                {{ siswa.agama || '-' }}
                            </td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-1 align-top font-bold text-slate-700">
                                9. Alamat Siswa
                            </td>
                            <td class="text-center align-top">:</td>
                            <td class="py-1 leading-snug text-slate-900">
                                {{ siswa.alamat_siswa || '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Sub-bagian 10: Orang Tua -->
                <div class="border-t-2 border-slate-400 pt-2">
                    <div
                        class="mb-1 text-[11px] font-bold tracking-wide text-slate-900 uppercase"
                    >
                        10. DATA ORANG TUA / WALI
                    </div>
                    <div class="space-y-1 pl-2 text-[10.5px]">
                        <div>
                            <span class="font-semibold text-slate-700"
                                >Nama Orang Tua:</span
                            >
                            <span class="ml-1 font-bold text-slate-900">{{
                                siswa.nama_orang_tua || '-'
                            }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700"
                                >a. Pekerjaan:</span
                            >
                            <span class="ml-1 text-slate-900">{{
                                siswa.pekerjaan_orang_tua || '-'
                            }}</span>
                        </div>
                        <div>
                            <span class="align-top font-semibold text-slate-700"
                                >b. Alamat Rumah:</span
                            >
                            <span class="ml-1 leading-tight text-slate-900">{{
                                siswa.alamat_orang_tua || '-'
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Sub-bagian 11: Diterima Menjadi Siswa -->
                <div class="border-t border-slate-300 pt-2">
                    <div
                        class="mb-1 text-[11px] font-bold tracking-wide text-slate-900 uppercase"
                    >
                        11. DITERIMA MENJADI SISWA
                    </div>
                    <div class="space-y-1 pl-2 text-[10.5px]">
                        <div>
                            <span class="font-semibold text-slate-700"
                                >a. Mulai Tanggal:</span
                            >
                            <span class="ml-1 font-medium text-slate-900">{{
                                formatDate(siswa.diterima_tanggal)
                            }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700"
                                >b. Asal Sekolah:</span
                            >
                            <span class="ml-1 font-bold text-slate-900">{{
                                siswa.diterima_asal_sekolah || '-'
                            }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700"
                                >c. Nomor STTB/Ijazah:</span
                            >
                            <span class="ml-1 font-mono text-slate-900">{{
                                siswa.diterima_nomor_sttb || '-'
                            }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700"
                                >d. Tanggal STTB/Ijazah:</span
                            >
                            <span class="ml-1 text-slate-900">{{
                                formatDate(siswa.diterima_tanggal_sttb)
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Sub-bagian 12: Meninggalkan Sekolah -->
                <div class="border-t border-slate-300 pt-2">
                    <div
                        class="mb-1 text-[11px] font-bold tracking-wide text-slate-900 uppercase"
                    >
                        12. MENINGGALKAN SEKOLAH
                    </div>
                    <div class="space-y-1 pl-2 text-[10.5px]">
                        <div>
                            <span class="font-semibold text-slate-700"
                                >a. Tanggal:</span
                            >
                            <span class="ml-1 text-slate-900">{{
                                formatDate(siswa.keluar_tanggal)
                            }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700"
                                >b. Alasan:</span
                            >
                            <span class="ml-1 text-slate-900 italic">{{
                                siswa.keluar_alasan || '-'
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Sub-bagian 13: Tamat Belajar -->
                <div class="border-t border-slate-300 pt-2">
                    <div
                        class="mb-1 text-[11px] font-bold tracking-wide text-slate-900 uppercase"
                    >
                        13. TAMAT BELAJAR / LULUS
                    </div>
                    <div class="space-y-1 pl-2 text-[10.5px]">
                        <div>
                            <span class="font-semibold text-slate-700"
                                >a. Nomor STTB/Ijazah:</span
                            >
                            <span
                                class="ml-1 font-mono font-bold text-slate-900"
                                >{{ siswa.tamat_nomor_sttb || '-' }}</span
                            >
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700"
                                >b. Tanggal STTB/Ijazah:</span
                            >
                            <span class="ml-1 font-medium text-slate-900">{{
                                formatDate(siswa.tamat_tanggal_sttb)
                            }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700"
                                >Keterangan Lain-Lain:</span
                            >
                            <span class="ml-1 text-slate-900">{{
                                siswa.keterangan_lain_lain || '-'
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Kotak Bingkai PasPhoto Ukuran Proporsional 3x4 cm di Bagian Kiri Bawah -->
                <div
                    class="flex flex-col items-center border-t-2 border-slate-900 pt-3"
                >
                    <div
                        class="pasphoto-frame relative flex h-[4cm] w-[3cm] flex-col items-center justify-center overflow-hidden border-2 border-dashed border-slate-800 bg-white p-1 shadow-xs"
                    >
                        <img
                            v-if="siswa.pasfoto_url"
                            :src="siswa.pasfoto_url"
                            :alt="siswa.nama_lengkap"
                            class="h-full w-full rounded-xs object-cover"
                        />
                        <div v-else class="p-2 text-center text-slate-400">
                            <UserCheck
                                class="mx-auto mb-1 h-8 w-8 text-slate-300"
                            />
                            <span
                                class="block text-[10px] leading-tight font-bold uppercase"
                                >Pasphoto</span
                            >
                            <span class="block text-[9px]">3 x 4 cm</span>
                        </div>
                    </div>
                    <span
                        class="mt-1 text-[10px] font-bold text-slate-600 uppercase"
                        >Pasfoto Siswa 3x4</span
                    >
                </div>
            </div>

            <!-- ================= PANEL KANAN (68% - TABEL MATRIKS NILAI SEMESTER I - X) ================= -->
            <div
                class="panel-kanan col-span-12 overflow-x-auto rounded border-2 border-slate-900 lg:col-span-8"
            >
                <table class="w-full border-collapse text-center text-[10.5px]">
                    <!-- Baris 1 & 2: Header Kolom Bertingkat -->
                    <thead>
                        <tr
                            class="bg-blue-900 text-[10px] font-bold text-white"
                        >
                            <th
                                rowspan="2"
                                class="w-6 border border-slate-900 px-1.5 py-1"
                            >
                                NO
                            </th>
                            <th
                                rowspan="2"
                                class="min-w-[180px] border border-slate-900 px-2 py-1 text-left"
                            >
                                PROGRAM PENDIDIKAN DAN PELATIHAN
                            </th>
                            <!-- 10 Kolom THN PELAJARAN -->
                            <th
                                v-for="sem in semesters"
                                :key="`thn-${sem}`"
                                class="min-w-[34px] border border-slate-900 px-1 py-0.5 text-[9px] font-semibold whitespace-nowrap"
                            >
                                {{ getTahunPelajaranForSemester(sem) }}
                            </th>
                            <th
                                rowspan="2"
                                class="vertical-text w-10 border border-slate-900 px-1 py-1 text-[9px]"
                            >
                                KET
                            </th>
                        </tr>
                        <tr
                            class="bg-blue-950 text-[10px] font-bold text-white"
                        >
                            <th
                                v-for="(rom, idx) in romanSemesters"
                                :key="`rom-${rom}`"
                                class="cursor-pointer border border-slate-900 px-1 py-1 text-[10px] transition-colors hover:bg-blue-800"
                                :title="`Klik untuk edit Nilai Semester ${rom}`"
                                @click="openNilaiModal(idx + 1)"
                            >
                                {{ rom }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="text-slate-900">
                        <!-- ================= SEKSI A. MATA PELAJARAN UMUM ================= -->
                        <tr
                            class="bg-sky-100 text-left font-black text-blue-950"
                        >
                            <td
                                class="border border-slate-900 px-1.5 py-1 text-center font-bold"
                            >
                                A
                            </td>
                            <td
                                colspan="12"
                                class="border border-slate-900 px-2 py-1 text-[10.5px] tracking-wider uppercase"
                            >
                                KELOMPOK MATA PELAJARAN UMUM
                            </td>
                        </tr>

                        <tr
                            v-for="(mapel, mIdx) in mapelUmum"
                            :key="mapel.id"
                            class="transition-colors hover:bg-slate-50"
                        >
                            <td
                                class="border border-slate-900 px-1 py-1 text-center font-semibold"
                            >
                                {{ mIdx + 1 }}
                            </td>
                            <td
                                class="border border-slate-900 px-2 py-1 text-left font-medium"
                            >
                                {{ mapel.nama_mapel }}
                                <span
                                    v-if="mapel.kode_mapel === 'PAI'"
                                    class="block text-[9px] text-slate-500 italic"
                                >
                                    (Sesuai agama yang dianut siswa:
                                    {{ siswa.agama || 'Islam' }})
                                </span>
                            </td>

                            <!-- Nilai Semester 1 - 10 -->
                            <td
                                v-for="sem in semesters"
                                :key="`val-${mapel.id}-${sem}`"
                                class="border border-slate-900 px-1 py-1 font-mono text-[10px]"
                                :class="
                                    academicMap[`${mapel.id}_${sem}`]
                                        ? 'font-bold'
                                        : 'text-slate-300'
                                "
                            >
                                {{ academicMap[`${mapel.id}_${sem}`] ?? '-' }}
                            </td>

                            <td
                                class="border border-slate-900 px-1 py-1 text-[9px] text-slate-500"
                            >
                                -
                            </td>
                        </tr>

                        <!-- ================= SEKSI B. MATA PELAJARAN KEJURUAN ================= -->
                        <tr
                            class="bg-sky-100 text-left font-black text-blue-950"
                        >
                            <td
                                class="border border-slate-900 px-1.5 py-1 text-center font-bold"
                            >
                                B
                            </td>
                            <td
                                colspan="12"
                                class="border border-slate-900 px-2 py-1 text-[10.5px] tracking-wider uppercase"
                            >
                                KELOMPOK MATA PELAJARAN KEJURUAN
                            </td>
                        </tr>

                        <tr
                            v-for="(mapel, mIdx) in mapelKejuruan"
                            :key="mapel.id"
                            class="transition-colors hover:bg-slate-50"
                        >
                            <td
                                class="border border-slate-900 px-1 py-1 text-center font-semibold"
                            >
                                {{ mIdx + 1 }}
                            </td>
                            <td
                                class="border border-slate-900 px-2 py-1 text-left font-medium"
                            >
                                {{ mapel.nama_mapel }}
                            </td>

                            <!-- Nilai Semester 1 - 10 -->
                            <td
                                v-for="sem in semesters"
                                :key="`val-b-${mapel.id}-${sem}`"
                                class="border border-slate-900 px-1 py-1 font-mono text-[10px]"
                                :class="
                                    academicMap[`${mapel.id}_${sem}`]
                                        ? 'font-bold'
                                        : 'text-slate-300'
                                "
                            >
                                {{ academicMap[`${mapel.id}_${sem}`] ?? '-' }}
                            </td>

                            <td
                                class="border border-slate-900 px-1 py-1 text-[9px] text-slate-500"
                            >
                                -
                            </td>
                        </tr>

                        <!-- ================= SEKSI PKL & EKSTRAKURIKULER ================= -->
                        <tr
                            class="bg-blue-50 text-left font-bold text-blue-900"
                        >
                            <td
                                class="border border-slate-900 px-1.5 py-0.5 text-center font-bold"
                            >
                                C
                            </td>
                            <td
                                colspan="12"
                                class="border border-slate-900 px-2 py-0.5 text-[10px] tracking-wide uppercase"
                            >
                                PRAKTIK KERJA LAPANGAN (PKL) & KEGIATAN EKSTRA
                                KURIKULER
                            </td>
                        </tr>

                        <!-- Praktik Kerja Lapangan (PKL) -->
                        <tr>
                            <td
                                class="border border-slate-900 px-1 py-1 text-center font-semibold"
                            >
                                1
                            </td>
                            <td
                                class="border border-slate-900 px-2 py-1 text-left font-semibold"
                            >
                                Praktik Kerja Lapangan (PKL)
                            </td>
                            <td
                                v-for="sem in semesters"
                                :key="`pkl-${sem}`"
                                class="border border-slate-900 px-1 py-1 text-[10px] font-bold"
                            >
                                {{ rekapMap[sem]?.nilai_pkl || '-' }}
                            </td>
                            <td
                                class="border border-slate-900 px-1 py-1 text-[9px] text-slate-400"
                            >
                                -
                            </td>
                        </tr>

                        <!-- Ekstrakurikuler -->
                        <tr
                            v-for="(ekskul, eIdx) in ekskulList"
                            :key="ekskul.id"
                        >
                            <td
                                class="border border-slate-900 px-1 py-1 text-center font-semibold"
                            >
                                {{ eIdx + 2 }}
                            </td>
                            <td
                                class="border border-slate-900 px-2 py-1 text-left font-medium"
                            >
                                {{ ekskul.nama_ekskul }}
                            </td>
                            <td
                                v-for="sem in semesters"
                                :key="`ekskul-${ekskul.id}-${sem}`"
                                class="border border-slate-900 px-1 py-1 text-[10px] font-bold"
                            >
                                {{ ekskulMap[`${ekskul.id}_${sem}`] || '-' }}
                            </td>
                            <td
                                class="border border-slate-900 px-1 py-1 text-[9px] text-slate-400"
                            >
                                -
                            </td>
                        </tr>

                        <!-- ================= SEKSI REKAPITULASI BAWAH ================= -->
                        <!-- JUMLAH NILAI -->
                        <tr class="bg-sky-50 font-bold">
                            <td
                                colspan="2"
                                class="border border-slate-900 px-3 py-1 text-left font-black text-blue-950 uppercase"
                            >
                                JUMLAH NILAI
                            </td>
                            <td
                                v-for="sem in semesters"
                                :key="`jml-${sem}`"
                                class="border border-slate-900 px-1 py-1 font-mono text-[10px] font-black text-blue-900"
                            >
                                {{ rekapMap[sem]?.jumlah_nilai ?? '-' }}
                            </td>
                            <td
                                class="border border-slate-900 px-1 py-1 text-[9px] text-slate-400"
                            >
                                -
                            </td>
                        </tr>

                        <!-- NILAI RATA-RATA -->
                        <tr class="bg-indigo-50/70 font-bold">
                            <td
                                colspan="2"
                                class="border border-slate-900 px-3 py-1 text-left font-black text-indigo-950 uppercase"
                            >
                                NILAI RATA-RATA
                            </td>
                            <td
                                v-for="sem in semesters"
                                :key="`avg-${sem}`"
                                class="border border-slate-900 px-1 py-1 font-mono text-[10px] font-black text-indigo-900"
                            >
                                {{ rekapMap[sem]?.nilai_rata_rata ?? '-' }}
                            </td>
                            <td
                                class="border border-slate-900 px-1 py-1 text-[9px] text-slate-400"
                            >
                                -
                            </td>
                        </tr>

                        <!-- RANKING -->
                        <tr class="bg-amber-50/40">
                            <td
                                colspan="2"
                                class="border border-slate-900 px-3 py-1 text-left font-bold text-slate-800"
                            >
                                RANKING
                            </td>
                            <td
                                v-for="sem in semesters"
                                :key="`rank-${sem}`"
                                class="border border-slate-900 px-1 py-1 font-mono text-[10px] font-bold text-slate-900"
                            >
                                {{ rekapMap[sem]?.ranking ?? '-' }}
                            </td>
                            <td
                                class="border border-slate-900 px-1 py-1 text-[9px] text-slate-400"
                            >
                                -
                            </td>
                        </tr>

                        <!-- ABSENSI: SAKIT -->
                        <tr>
                            <td
                                colspan="2"
                                class="border border-slate-900 px-3 py-0.5 text-left font-medium text-slate-700"
                            >
                                Ketidakhadiran: SAKIT (HARI)
                            </td>
                            <td
                                v-for="sem in semesters"
                                :key="`sakit-${sem}`"
                                class="border border-slate-900 px-1 py-0.5 font-mono text-[10px]"
                            >
                                {{ rekapMap[sem]?.sakit_hari ?? 0 }}
                            </td>
                            <td
                                class="border border-slate-900 px-1 py-0.5 text-[9px] text-slate-400"
                            >
                                -
                            </td>
                        </tr>

                        <!-- ABSENSI: IZIN -->
                        <tr>
                            <td
                                colspan="2"
                                class="border border-slate-900 px-3 py-0.5 text-left font-medium text-slate-700"
                            >
                                Ketidakhadiran: IZIN (HARI)
                            </td>
                            <td
                                v-for="sem in semesters"
                                :key="`izin-${sem}`"
                                class="border border-slate-900 px-1 py-0.5 font-mono text-[10px]"
                            >
                                {{ rekapMap[sem]?.izin_hari ?? 0 }}
                            </td>
                            <td
                                class="border border-slate-900 px-1 py-0.5 text-[9px] text-slate-400"
                            >
                                -
                            </td>
                        </tr>

                        <!-- ABSENSI: TANPA KABAR -->
                        <tr>
                            <td
                                colspan="2"
                                class="border border-slate-900 px-3 py-0.5 text-left font-medium text-slate-700"
                            >
                                Ketidakhadiran: TANPA KABAR (HARI)
                            </td>
                            <td
                                v-for="sem in semesters"
                                :key="`alpa-${sem}`"
                                class="border border-slate-900 px-1 py-0.5 font-mono text-[10px]"
                            >
                                {{ rekapMap[sem]?.tanpa_kabar_hari ?? 0 }}
                            </td>
                            <td
                                class="border border-slate-900 px-1 py-0.5 text-[9px] text-slate-400"
                            >
                                -
                            </td>
                        </tr>

                        <!-- ABSENSI: JUMLAH -->
                        <tr class="bg-slate-100 font-bold">
                            <td
                                colspan="2"
                                class="border border-slate-900 px-3 py-0.5 text-left font-bold text-slate-900"
                            >
                                JUMLAH KETIDAKHADIRAN (HARI)
                            </td>
                            <td
                                v-for="sem in semesters"
                                :key="`tot-absen-${sem}`"
                                class="border border-slate-900 px-1 py-0.5 font-mono text-[10px] font-bold"
                            >
                                {{ rekapMap[sem]?.jumlah_absen_hari ?? 0 }}
                            </td>
                            <td
                                class="border border-slate-900 px-1 py-0.5 text-[9px] text-slate-400"
                            >
                                -
                            </td>
                        </tr>

                        <!-- STATUS AKHIR TAHUN (Digabung per 2 semester: Sem I-II, III-IV, V-VI, VII-VIII, IX-X) -->
                        <tr
                            class="border-t-2 border-slate-900 bg-emerald-50/60 font-bold"
                        >
                            <td
                                colspan="2"
                                class="border border-slate-900 px-3 py-1.5 text-left font-black text-emerald-950 uppercase"
                            >
                                STATUS KENAIKAN / KELULUSAN
                            </td>
                            <!-- Span per 2 semester -->
                            <td
                                colspan="2"
                                class="border border-slate-900 px-1 py-1 text-[9.5px]"
                            >
                                <div
                                    v-if="
                                        rekapMap[2]?.status_kenaikan ===
                                        'NAIK_TINGKAT'
                                    "
                                    class="font-extrabold text-emerald-800"
                                >
                                    NAIK KE
                                    {{ rekapMap[2]?.tingkat_tujuan || 'XI' }}
                                </div>
                                <div
                                    v-else-if="
                                        rekapMap[2]?.status_kenaikan ===
                                        'TIDAK_NAIK'
                                    "
                                    class="font-bold text-rose-700"
                                >
                                    TIDAK NAIK
                                </div>
                                <div v-else class="text-slate-400">-</div>
                            </td>
                            <td
                                colspan="2"
                                class="border border-slate-900 px-1 py-1 text-[9.5px]"
                            >
                                <div
                                    v-if="
                                        rekapMap[4]?.status_kenaikan ===
                                        'NAIK_TINGKAT'
                                    "
                                    class="font-extrabold text-emerald-800"
                                >
                                    NAIK KE
                                    {{ rekapMap[4]?.tingkat_tujuan || 'XII' }}
                                </div>
                                <div
                                    v-else-if="
                                        rekapMap[4]?.status_kenaikan ===
                                        'TIDAK_NAIK'
                                    "
                                    class="font-bold text-rose-700"
                                >
                                    TIDAK NAIK
                                </div>
                                <div v-else class="text-slate-400">-</div>
                            </td>
                            <td
                                colspan="2"
                                class="border border-slate-900 px-1 py-1 text-[9.5px]"
                            >
                                <div
                                    v-if="
                                        rekapMap[6]?.status_kenaikan === 'LULUS'
                                    "
                                    class="font-black text-blue-900"
                                >
                                    LULUS TAMAT
                                </div>
                                <div
                                    v-else-if="
                                        rekapMap[6]?.status_kenaikan ===
                                        'NAIK_TINGKAT'
                                    "
                                    class="font-bold text-emerald-800"
                                >
                                    NAIK KE
                                    {{ rekapMap[6]?.tingkat_tujuan || 'XIII' }}
                                </div>
                                <div
                                    v-else-if="
                                        rekapMap[6]?.status_kenaikan ===
                                        'TIDAK_NAIK'
                                    "
                                    class="font-bold text-rose-700"
                                >
                                    TIDAK NAIK
                                </div>
                                <div v-else class="text-slate-400">-</div>
                            </td>
                            <td
                                colspan="2"
                                class="border border-slate-900 px-1 py-1 text-[9.5px]"
                            >
                                <div
                                    v-if="rekapMap[8]?.status_kenaikan"
                                    class="font-bold text-slate-800"
                                >
                                    {{ rekapMap[8]?.status_kenaikan }}
                                </div>
                                <div v-else class="text-slate-400">-</div>
                            </td>
                            <td
                                colspan="2"
                                class="border border-slate-900 px-1 py-1 text-[9.5px]"
                            >
                                <div
                                    v-if="rekapMap[10]?.status_kenaikan"
                                    class="font-bold text-slate-800"
                                >
                                    {{ rekapMap[10]?.status_kenaikan }}
                                </div>
                                <div v-else class="text-slate-400">-</div>
                            </td>
                            <td
                                class="border border-slate-900 px-1 py-1 text-[9px] text-slate-400"
                            >
                                -
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tanda Tangan & Pengesahan Bawah Kanvas -->
        <div
            class="mt-6 flex items-start justify-between border-t-2 border-slate-900 pt-4 text-xs font-semibold text-slate-900"
        >
            <div class="w-64 text-center">
                <p class="text-[11px] text-slate-600">Mengetahui,</p>
                <p class="font-bold">Orang Tua / Wali Siswa</p>
                <div class="h-16"></div>
                <p
                    class="inline-block min-w-[180px] border-b border-slate-800 font-bold uppercase"
                >
                    {{
                        siswa.nama_orang_tua ||
                        '( .......................................... )'
                    }}
                </p>
            </div>

            <div class="w-64 text-center">
                <p class="text-[11px] text-slate-600">Ditetapkan di: Bandung</p>
                <p class="font-bold">Kepala Sekolah SMK,</p>
                <div class="h-16"></div>
                <p
                    class="inline-block min-w-[180px] border-b border-slate-800 font-bold uppercase"
                >
                    ( .......................................... )
                </p>
                <p class="text-[10px] text-slate-600">
                    NIP. ..........................................
                </p>
            </div>
        </div>
    </div>

    <!-- ================= MODAL INPUT / EDIT NILAI SEMESTER ================= -->
    <div
        v-if="isNilaiModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-xs print:hidden"
    >
        <div
            class="flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        >
            <!-- Modal Header -->
            <div
                class="flex items-center justify-between bg-blue-900 px-6 py-4 text-white"
            >
                <div>
                    <h3
                        class="flex items-center gap-2 text-base font-black tracking-wide uppercase"
                    >
                        <BookOpen class="h-5 w-5 text-sky-400" />
                        Input Nilai & Prestasi Semester
                        {{ romanSemesters[selectedSemester - 1] }} (Semester
                        {{ selectedSemester }})
                    </h3>
                    <p class="text-xs text-blue-200">
                        Siswa:
                        <strong class="text-white">{{
                            siswa.nama_lengkap
                        }}</strong>
                        | NISN:
                        <strong class="text-white">{{ siswa.nisn }}</strong>
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 text-blue-200 hover:text-white"
                    @click="closeNilaiModal"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Modal Body -->
            <form
                @submit.prevent="submitNilaiForm"
                class="flex-1 space-y-6 overflow-y-auto p-6 text-xs"
            >
                <!-- Semester & Tahun Selector -->
                <div
                    class="grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2"
                >
                    <div>
                        <label class="mb-1 block font-bold text-slate-700"
                            >Pilih Semester Ke *</label
                        >
                        <select
                            v-model.number="selectedSemester"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 font-bold text-blue-900"
                            @change="openNilaiModal(selectedSemester)"
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

                    <div>
                        <label class="mb-1 block font-bold text-slate-700"
                            >Tahun Pelajaran *</label
                        >
                        <select
                            v-model="nilaiForm.tahun_pelajaran_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 font-bold"
                        >
                            <option value="">
                                -- Pilih Tahun Pelajaran --
                            </option>
                            <option
                                v-for="t in tahunList"
                                :key="t.id"
                                :value="t.id"
                            >
                                {{ t.tahun_pelajaran }}
                                {{ t.is_aktif ? '(Aktif)' : '' }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Nilai Mata Pelajaran -->
                <div>
                    <h4
                        class="mb-3 border-b border-blue-100 pb-2 text-xs font-bold tracking-wide text-blue-900 uppercase"
                    >
                        Nilai Angka Mata Pelajaran (Skala 0 - 100)
                    </h4>

                    <div
                        class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3"
                    >
                        <div
                            v-for="(item, idx) in nilaiForm.nilai_akademik"
                            :key="item.mata_pelajaran_id"
                            class="flex flex-col justify-between rounded-lg border border-slate-200 bg-slate-50/50 p-2.5"
                        >
                            <label
                                class="mb-1 block truncate text-[11px] font-semibold text-slate-800"
                            >
                                {{
                                    [...mapelUmum, ...mapelKejuruan].find(
                                        (m) => m.id === item.mata_pelajaran_id,
                                    )?.nama_mapel
                                }}
                            </label>
                            <input
                                v-model="item.nilai_angka"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                placeholder="Contoh: 85"
                                class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 font-mono text-xs font-bold text-blue-900"
                            />
                        </div>
                    </div>
                </div>

                <!-- PKL & Ekstrakurikuler -->
                <div>
                    <h4
                        class="mb-3 border-b border-blue-100 pb-2 text-xs font-bold tracking-wide text-blue-900 uppercase"
                    >
                        Praktik Kerja Lapangan (PKL) & Ekstrakurikuler
                    </h4>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- PKL -->
                        <div
                            class="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3"
                        >
                            <label class="block font-bold text-slate-800"
                                >Nilai Praktik Kerja Lapangan (PKL)</label
                            >
                            <input
                                v-model="nilaiForm.nilai_pkl"
                                type="text"
                                placeholder="Contoh: 88 / Sangat Baik"
                                class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-xs"
                            />
                            <input
                                v-model="nilaiForm.keterangan_pkl"
                                type="text"
                                placeholder="Keterangan PKL (Nama Perusahaan / Tempat)"
                                class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-xs"
                            />
                        </div>

                        <!-- Ekskul -->
                        <div
                            class="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3"
                        >
                            <label class="block font-bold text-slate-800"
                                >Kegiatan Ekstrakurikuler</label
                            >
                            <div
                                v-for="item in nilaiForm.nilai_ekskul"
                                :key="item.ekstrakurikuler_id"
                                class="flex items-center gap-2"
                            >
                                <span
                                    class="w-32 truncate text-[11px] font-semibold text-slate-700"
                                >
                                    {{
                                        ekskulList.find(
                                            (e) =>
                                                e.id ===
                                                item.ekstrakurikuler_id,
                                        )?.nama_ekskul
                                    }}:
                                </span>
                                <input
                                    v-model="item.nilai_predikat"
                                    type="text"
                                    placeholder="Predikat (A / B / C / 85)"
                                    class="flex-1 rounded border border-slate-300 bg-white px-2.5 py-1 text-xs"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ranking & Absensi & Status Kenaikan -->
                <div>
                    <h4
                        class="mb-3 border-b border-blue-100 pb-2 text-xs font-bold tracking-wide text-blue-900 uppercase"
                    >
                        Ranking, Absensi (Hari) & Status Kenaikan
                    </h4>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Ranking Kelas</label
                            >
                            <input
                                v-model="nilaiForm.ranking"
                                type="number"
                                min="1"
                                placeholder="Contoh: 1"
                                class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-xs"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Sakit (Hari)</label
                            >
                            <input
                                v-model.number="nilaiForm.sakit_hari"
                                type="number"
                                min="0"
                                class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-xs"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Izin (Hari)</label
                            >
                            <input
                                v-model.number="nilaiForm.izin_hari"
                                type="number"
                                min="0"
                                class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-xs"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Tanpa Kabar (Hari)</label
                            >
                            <input
                                v-model.number="nilaiForm.tanpa_kabar_hari"
                                type="number"
                                min="0"
                                class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-xs"
                            />
                        </div>
                    </div>

                    <!-- Status Akhir Tahun -->
                    <div
                        class="mt-3 grid grid-cols-1 gap-3 border-t border-slate-100 pt-3 sm:grid-cols-2"
                    >
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Status Kenaikan / Kelulusan (Semester
                                Genap)</label
                            >
                            <select
                                v-model="nilaiForm.status_kenaikan"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs"
                            >
                                <option value="">
                                    -- Kosongkan (Bukan Akhir Tingkat) --
                                </option>
                                <option value="NAIK_TINGKAT">
                                    NAIK TINGKAT
                                </option>
                                <option value="TIDAK_NAIK">TIDAK NAIK</option>
                                <option value="LULUS">LULUS / TAMAT</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Tingkat Tujuan (Jika Naik Tingkat)</label
                            >
                            <input
                                v-model="nilaiForm.tingkat_tujuan"
                                type="text"
                                placeholder="Contoh: XI TKJ / XII TKJ"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs"
                            />
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div
                    class="sticky bottom-0 flex items-center justify-end gap-3 border-t border-slate-200 bg-white pt-4"
                >
                    <button
                        type="button"
                        class="cursor-pointer rounded-lg border border-slate-300 px-4 py-2 font-bold text-slate-700 transition-colors hover:bg-slate-100"
                        @click="closeNilaiModal"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="nilaiForm.processing"
                        class="flex cursor-pointer items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2 font-bold text-white shadow-md transition-all hover:bg-blue-700 active:scale-95 disabled:opacity-50"
                    >
                        <Save class="h-4 w-4" />
                        <span v-if="nilaiForm.processing"
                            >Menyimpan Nilai...</span
                        >
                        <span v-else
                            >Simpan Nilai Semester
                            {{ romanSemesters[selectedSemester - 1] }}</span
                        >
                    </button>
                </div>
            </form>
        </div>
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
    table,
    th,
    td {
        border-color: #000000 !important;
    }

    .bg-blue-900 {
        background-color: #1e3a8a !important;
        color: white !important;
    }

    .bg-blue-950 {
        background-color: #172554 !important;
        color: white !important;
    }

    .bg-sky-100 {
        background-color: #e0f2fe !important;
        color: black !important;
    }

    .bg-blue-50 {
        background-color: #eff6ff !important;
    }

    .bg-indigo-50\/70 {
        background-color: #eef2ff !important;
    }

    .bg-emerald-50\/60 {
        background-color: #ecfdf5 !important;
    }
}
</style>
