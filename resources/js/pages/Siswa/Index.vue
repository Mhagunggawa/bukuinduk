<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Search,
    Plus,
    FileSpreadsheet,
    BookOpen,
    Edit3,
    Trash2,
    X,
    Filter,
    UserCheck,
    ChevronLeft,
    ChevronRight,
    Camera,
} from '@lucide/vue';

interface Konsentrasi {
    id: number;
    nama_konsentrasi: string;
    kode_singkat: string;
}

interface SiswaItem {
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
    konsentrasi_keahlian_id: number | null;
    konsentrasi_keahlian?: Konsentrasi;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    siswa: {
        data: SiswaItem[];
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
        links: PaginationLink[];
    };
    jurusanList: Konsentrasi[];
    kelasList: string[];
    filters: {
        search: string;
        jurusan_id: string | number;
        kelas: string;
        status: string;
    };
}>();

// Filter states
const search = ref(props.filters.search || '');
const jurusanId = ref(props.filters.jurusan_id || '');
const kelas = ref(props.filters.kelas || '');
const status = ref(props.filters.status || '');

let searchDebounceTimer: any = null;

const applyFilters = () => {
    router.get(
        '/siswa',
        {
            search: search.value,
            jurusan_id: jurusanId.value,
            kelas: kelas.value,
            status: status.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(search, () => {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        applyFilters();
    }, 400);
});

watch([jurusanId, kelas, status], () => {
    applyFilters();
});

const resetFilters = () => {
    search.value = '';
    jurusanId.value = '';
    kelas.value = '';
    status.value = '';
    applyFilters();
};

// Modal Siswa (Create & Edit)
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingSiswaId = ref<number | null>(null);

const form = useForm({
    nis: '',
    nisn: '',
    nama_lengkap: '',
    jenis_kelamin: 'L',
    konsentrasi_keahlian_id: '',
    kelas_saat_ini: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    warga_negara: 'WNI',
    agama: 'Islam',
    alamat_siswa: '',
    nama_orang_tua: '',
    pekerjaan_orang_tua: '',
    alamat_orang_tua: '',
    diterima_tanggal: '',
    diterima_asal_sekolah: '',
    diterima_nomor_sttb: '',
    diterima_tanggal_sttb: '',
    keluar_tanggal: '',
    keluar_alasan: '',
    tamat_nomor_sttb: '',
    tamat_tanggal_sttb: '',
    keterangan_lain_lain: '',
    status_siswa: 'AKTIF',
    pasfoto: null as File | null,
});

const openCreateModal = () => {
    isEditing.value = false;
    editingSiswaId.value = null;
    form.reset();
    form.clearErrors();
    form.warga_negara = 'WNI';
    form.status_siswa = 'AKTIF';
    form.jenis_kelamin = 'L';
    isModalOpen.value = true;
};

const openEditModal = (item: SiswaItem) => {
    isEditing.value = true;
    editingSiswaId.value = item.id;
    form.reset();
    form.clearErrors();

    form.nis = item.nis || '';
    form.nisn = item.nisn || '';
    form.nama_lengkap = item.nama_lengkap || '';
    form.jenis_kelamin = item.jenis_kelamin || 'L';
    form.konsentrasi_keahlian_id = item.konsentrasi_keahlian_id
        ? String(item.konsentrasi_keahlian_id)
        : '';
    form.kelas_saat_ini = item.kelas_saat_ini || '';
    form.tempat_lahir = item.tempat_lahir || '';
    form.tanggal_lahir = item.tanggal_lahir
        ? item.tanggal_lahir.substring(0, 10)
        : '';
    form.warga_negara = item.warga_negara || 'WNI';
    form.agama = item.agama || 'Islam';
    form.alamat_siswa = item.alamat_siswa || '';
    form.nama_orang_tua = item.nama_orang_tua || '';
    form.pekerjaan_orang_tua = item.pekerjaan_orang_tua || '';
    form.alamat_orang_tua = item.alamat_orang_tua || '';
    form.diterima_tanggal = item.diterima_tanggal
        ? item.diterima_tanggal.substring(0, 10)
        : '';
    form.diterima_asal_sekolah = item.diterima_asal_sekolah || '';
    form.diterima_nomor_sttb = item.diterima_nomor_sttb || '';
    form.diterima_tanggal_sttb = item.diterima_tanggal_sttb
        ? item.diterima_tanggal_sttb.substring(0, 10)
        : '';
    form.keluar_tanggal = item.keluar_tanggal
        ? item.keluar_tanggal.substring(0, 10)
        : '';
    form.keluar_alasan = item.keluar_alasan || '';
    form.tamat_nomor_sttb = item.tamat_nomor_sttb || '';
    form.tamat_tanggal_sttb = item.tamat_tanggal_sttb
        ? item.tamat_tanggal_sttb.substring(0, 10)
        : '';
    form.keterangan_lain_lain = item.keterangan_lain_lain || '';
    form.status_siswa = item.status_siswa || 'AKTIF';
    form.pasfoto = null;

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handlePhotoChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        form.pasfoto = input.files[0];
    }
};

const submitForm = () => {
    if (isEditing.value && editingSiswaId.value) {
        // Because files with PUT requires FormData with _method='PUT'
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(`/siswa/${editingSiswaId.value}`, {
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        form.post('/siswa', {
            onSuccess: () => {
                closeModal();
            },
        });
    }
};

const deleteSiswa = (item: SiswaItem) => {
    if (
        confirm(
            `Apakah Anda yakin ingin menghapus data siswa ${item.nama_lengkap} (NISN: ${item.nisn})? Data nilai dan riwayat buku induk siswa ini akan ikut terhapus.`,
        )
    ) {
        router.delete(`/siswa/${item.id}`);
    }
};
</script>

<template>
    <Head title="Direktori Siswa" />

    <div class="space-y-5">
        <!-- Header Section -->
        <div
            class="flex flex-col justify-between gap-4 rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs sm:flex-row sm:items-center"
        >
            <div>
                <h1
                    class="flex items-center gap-2 text-xl font-black tracking-tight text-blue-950 uppercase"
                >
                    <Users class="h-6 w-6 text-blue-600" />
                    Direktori Siswa
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    Pencarian cepat, pengelolaan biodata siswa, dan akses
                    langsung ke Lembar Hasil Prestasi Buku Induk.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <Link
                    href="/import"
                    class="inline-flex items-center gap-2 rounded-lg border border-blue-600 px-3.5 py-2 text-xs font-bold text-blue-600 shadow-2xs transition-colors hover:bg-blue-50"
                >
                    <FileSpreadsheet class="h-4 w-4" />
                    <span>Import Excel</span>
                </Link>

                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-blue-700 active:scale-95"
                    @click="openCreateModal"
                >
                    <Plus class="h-4 w-4" />
                    <span>+ Tambah Siswa</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Controls Bar -->
        <div
            class="space-y-3 rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs"
        >
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Search Input -->
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari NIS, NISN, atau Nama Siswa..."
                        class="w-full rounded-lg border border-slate-300 bg-slate-50/50 py-2 pr-3 pl-9 text-xs transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <!-- Filter Jurusan -->
                <div>
                    <select
                        v-model="jurusanId"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Semua Konsentrasi Keahlian</option>
                        <option
                            v-for="jurusan in jurusanList"
                            :key="jurusan.id"
                            :value="jurusan.id"
                        >
                            {{ jurusan.nama_konsentrasi }} ({{
                                jurusan.kode_singkat
                            }})
                        </option>
                    </select>
                </div>

                <!-- Filter Kelas -->
                <div>
                    <select
                        v-model="kelas"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Semua Rombel / Kelas</option>
                        <option v-for="k in kelasList" :key="k" :value="k">
                            {{ k }}
                        </option>
                    </select>
                </div>

                <!-- Filter Status -->
                <div>
                    <select
                        v-model="status"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Semua Status Siswa</option>
                        <option value="AKTIF">AKTIF</option>
                        <option value="TAMAT">TAMAT / LULUS</option>
                        <option value="KELUAR">PINDAH / KELUAR</option>
                    </select>
                </div>
            </div>

            <!-- Active Filter Indicator & Reset Button -->
            <div
                v-if="search || jurusanId || kelas || status"
                class="flex items-center justify-between border-t border-slate-100 pt-2 text-xs text-slate-500"
            >
                <span class="flex items-center gap-1.5 font-medium">
                    <Filter class="h-3.5 w-3.5 text-blue-600" />
                    Menampilkan hasil filter (Total:
                    <span class="font-bold text-slate-800">{{
                        siswa.total
                    }}</span>
                    siswa)
                </span>
                <button
                    type="button"
                    class="font-semibold text-blue-600 hover:text-blue-800 hover:underline"
                    @click="resetFilters"
                >
                    Reset Filter
                </button>
            </div>
        </div>

        <!-- Table Siswa -->
        <div
            class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <!-- Header Tabel Utama: bg-blue-900 text-white -->
                    <thead
                        class="bg-blue-900 text-[11px] font-bold tracking-wider text-white uppercase select-none"
                    >
                        <tr>
                            <th class="w-12 px-3 py-3 text-center">Foto</th>
                            <th class="px-3 py-3 whitespace-nowrap">
                                NIS / NISN
                            </th>
                            <th class="px-3 py-3">Nama Lengkap</th>
                            <th class="w-10 px-3 py-3 text-center">L/P</th>
                            <th class="px-3 py-3 whitespace-nowrap">Kelas</th>
                            <th class="px-3 py-3 whitespace-nowrap">
                                Konsentrasi Keahlian
                            </th>
                            <th class="px-3 py-3 text-center whitespace-nowrap">
                                Status
                            </th>
                            <th
                                class="w-40 px-3 py-3 text-center whitespace-nowrap"
                            >
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr
                            v-for="item in siswa.data"
                            :key="item.id"
                            class="transition-colors hover:bg-blue-50/60"
                        >
                            <!-- Pasfoto mini -->
                            <td class="px-3 py-2.5 text-center">
                                <div
                                    class="mx-auto flex h-12 w-9 items-center justify-center overflow-hidden rounded border border-slate-300 bg-slate-100 text-slate-400 shadow-2xs"
                                >
                                    <img
                                        v-if="item.pasfoto_url"
                                        :src="item.pasfoto_url"
                                        :alt="item.nama_lengkap"
                                        class="h-full w-full object-cover"
                                    />
                                    <UserCheck
                                        v-else
                                        class="h-5 w-5 text-slate-300"
                                    />
                                </div>
                            </td>

                            <!-- NIS / NISN -->
                            <td
                                class="px-3 py-2.5 font-mono text-[11px] whitespace-nowrap"
                            >
                                <div class="font-bold text-slate-800">
                                    {{ item.nis }}
                                </div>
                                <div class="font-medium text-slate-500">
                                    {{ item.nisn }}
                                </div>
                            </td>

                            <!-- Nama Lengkap -->
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div
                                    class="font-bold text-slate-900 transition-colors hover:text-blue-600"
                                >
                                    <Link
                                        :href="`/siswa/${item.id}/buku-induk`"
                                    >
                                        {{ item.nama_lengkap }}
                                    </Link>
                                </div>
                                <div class="text-[10px] text-slate-500">
                                    {{ item.tempat_lahir || '-' }},
                                    {{
                                        item.tanggal_lahir
                                            ? item.tanggal_lahir.substring(
                                                  0,
                                                  10,
                                              )
                                            : '-'
                                    }}
                                </div>
                            </td>

                            <!-- Jenis Kelamin -->
                            <td
                                class="px-3 py-2.5 text-center font-semibold whitespace-nowrap"
                            >
                                <span
                                    :class="
                                        item.jenis_kelamin === 'L'
                                            ? 'bg-blue-50 text-blue-700'
                                            : 'bg-rose-50 text-rose-700'
                                    "
                                    class="rounded border border-current px-1.5 py-0.5 text-[10px] font-bold"
                                >
                                    {{ item.jenis_kelamin }}
                                </span>
                            </td>

                            <!-- Kelas -->
                            <td
                                class="px-3 py-2.5 font-semibold whitespace-nowrap text-slate-700"
                            >
                                {{ item.kelas_saat_ini || '-' }}
                            </td>

                            <!-- Konsentrasi Keahlian -->
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div
                                    v-if="item.konsentrasi_keahlian"
                                    class="font-medium text-slate-800"
                                >
                                    {{
                                        item.konsentrasi_keahlian
                                            .nama_konsentrasi
                                    }}
                                    <span class="font-bold text-blue-600"
                                        >({{
                                            item.konsentrasi_keahlian
                                                .kode_singkat
                                        }})</span
                                    >
                                </div>
                                <span v-else class="text-slate-400 italic"
                                    >Belum ditentukan</span
                                >
                            </td>

                            <!-- Status Siswa -->
                            <td
                                class="px-3 py-2.5 text-center whitespace-nowrap"
                            >
                                <span
                                    v-if="item.status_siswa === 'AKTIF'"
                                    class="inline-block rounded-full border border-emerald-300 bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800"
                                >
                                    AKTIF
                                </span>
                                <span
                                    v-else-if="item.status_siswa === 'TAMAT'"
                                    class="inline-block rounded-full border border-blue-300 bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold text-blue-800"
                                >
                                    TAMAT
                                </span>
                                <span
                                    v-else
                                    class="inline-block rounded-full border border-rose-300 bg-rose-100 px-2.5 py-0.5 text-[10px] font-bold text-rose-800"
                                >
                                    KELUAR
                                </span>
                            </td>

                            <!-- Tombol Aksi -->
                            <td
                                class="px-3 py-2.5 text-center whitespace-nowrap"
                            >
                                <div
                                    class="flex items-center justify-center gap-1.5"
                                >
                                    <!-- Buka Buku Induk -->
                                    <Link
                                        :href="`/siswa/${item.id}/buku-induk`"
                                        class="inline-flex items-center gap-1 rounded bg-blue-600 px-2.5 py-1 text-[11px] font-bold text-white shadow-2xs transition-colors hover:bg-blue-700"
                                        title="Buka Lembar Buku Induk"
                                    >
                                        <BookOpen class="h-3.5 w-3.5" />
                                        <span>Buku Induk</span>
                                    </Link>

                                    <!-- Edit -->
                                    <button
                                        type="button"
                                        class="cursor-pointer rounded p-1 text-slate-600 transition-colors hover:bg-blue-100 hover:text-blue-700"
                                        title="Edit Biodata Siswa"
                                        @click="openEditModal(item)"
                                    >
                                        <Edit3 class="h-3.5 w-3.5" />
                                    </button>

                                    <!-- Hapus -->
                                    <button
                                        type="button"
                                        class="cursor-pointer rounded p-1 text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600"
                                        title="Hapus Siswa"
                                        @click="deleteSiswa(item)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="siswa.data.length === 0">
                            <td
                                colspan="8"
                                class="py-10 text-center text-slate-400"
                            >
                                Tidak ada data siswa yang sesuai dengan kriteria
                                pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div
                class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 p-4 text-xs text-slate-600 sm:flex-row"
            >
                <div>
                    Menampilkan
                    <span class="font-bold text-slate-900">{{
                        siswa.from || 0
                    }}</span>
                    sampai
                    <span class="font-bold text-slate-900">{{
                        siswa.to || 0
                    }}</span>
                    dari total
                    <span class="font-bold text-slate-900">{{
                        siswa.total
                    }}</span>
                    siswa
                </div>

                <div class="flex items-center gap-1">
                    <template v-for="(link, i) in siswa.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="[
                                'rounded px-2.5 py-1.5 text-xs font-semibold transition-colors',
                                link.active
                                    ? 'bg-blue-600 text-white shadow-2xs'
                                    : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-100',
                            ]"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-2 py-1.5 text-xs text-slate-400"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah / Edit Siswa -->
    <div
        v-if="isModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-xs"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        >
            <!-- Modal Header -->
            <div
                class="flex items-center justify-between bg-blue-900 px-6 py-4 text-white"
            >
                <div>
                    <h3 class="text-base font-black tracking-wide uppercase">
                        {{
                            isEditing ? 'Edit Data Siswa' : 'Tambah Siswa Baru'
                        }}
                    </h3>
                    <p class="text-xs text-blue-200">
                        Isi informasi identitas lengkap siswa untuk dicatat pada
                        Buku Induk.
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 text-blue-200 hover:text-white"
                    @click="closeModal"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Modal Body (Form) -->
            <form
                @submit.prevent="submitForm"
                class="flex-1 space-y-6 overflow-y-auto p-6 text-xs"
            >
                <!-- Seksi 1: Identitas Pokok & Pasfoto -->
                <div>
                    <h4
                        class="mb-3 border-b border-blue-100 pb-2 text-xs font-bold tracking-wide text-blue-900 uppercase"
                    >
                        1. Identitas Pokok Siswa
                    </h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- Pasfoto Upload -->
                        <div
                            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3 text-center"
                        >
                            <div
                                class="mb-2 flex h-32 w-24 items-center justify-center overflow-hidden rounded border border-slate-200 bg-white shadow-2xs"
                            >
                                <img
                                    v-if="form.pasfoto"
                                    :src="URL.createObjectURL(form.pasfoto)"
                                    class="h-full w-full object-cover"
                                />
                                <Camera v-else class="h-8 w-8 text-slate-300" />
                            </div>
                            <label
                                class="cursor-pointer text-[11px] font-bold text-blue-600 hover:text-blue-800"
                            >
                                <span>Pilih Pasfoto 3x4</span>
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handlePhotoChange"
                                />
                            </label>
                            <span class="mt-0.5 text-[10px] text-slate-400"
                                >JPG / PNG, maks 2MB</span
                            >
                        </div>

                        <!-- NIS, NISN, Nama, JK, Jurusan, Kelas -->
                        <div
                            class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:col-span-2"
                        >
                            <div>
                                <label
                                    class="mb-1 block font-bold text-slate-700"
                                    >NIS (Wajib) *</label
                                >
                                <input
                                    v-model="form.nis"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                    :class="
                                        form.errors.nis
                                            ? 'border-rose-500 bg-rose-50'
                                            : 'border-slate-300'
                                    "
                                />
                                <span
                                    v-if="form.errors.nis"
                                    class="text-[10px] text-rose-600"
                                    >{{ form.errors.nis }}</span
                                >
                            </div>

                            <div>
                                <label
                                    class="mb-1 block font-bold text-slate-700"
                                    >NISN (Wajib, 10 Digit) *</label
                                >
                                <input
                                    v-model="form.nisn"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                    :class="
                                        form.errors.nisn
                                            ? 'border-rose-500 bg-rose-50'
                                            : 'border-slate-300'
                                    "
                                />
                                <span
                                    v-if="form.errors.nisn"
                                    class="text-[10px] text-rose-600"
                                    >{{ form.errors.nisn }}</span
                                >
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    class="mb-1 block font-bold text-slate-700"
                                    >Nama Lengkap Siswa *</label
                                >
                                <input
                                    v-model="form.nama_lengkap"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border px-3 py-2 uppercase focus:ring-2 focus:ring-blue-500"
                                    :class="
                                        form.errors.nama_lengkap
                                            ? 'border-rose-500 bg-rose-50'
                                            : 'border-slate-300'
                                    "
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-1 block font-bold text-slate-700"
                                    >Jenis Kelamin *</label
                                >
                                <select
                                    v-model="form.jenis_kelamin"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2"
                                >
                                    <option value="L">Laki-Laki (L)</option>
                                    <option value="P">Perempuan (P)</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1 block font-bold text-slate-700"
                                    >Konsentrasi Keahlian (Jurusan)</label
                                >
                                <select
                                    v-model="form.konsentrasi_keahlian_id"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2"
                                >
                                    <option value="">
                                        -- Pilih Jurusan --
                                    </option>
                                    <option
                                        v-for="j in jurusanList"
                                        :key="j.id"
                                        :value="j.id"
                                    >
                                        {{ j.nama_konsentrasi }} ({{
                                            j.kode_singkat
                                        }})
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1 block font-bold text-slate-700"
                                    >Rombel / Kelas Saat Ini</label
                                >
                                <input
                                    v-model="form.kelas_saat_ini"
                                    type="text"
                                    placeholder="Contoh: X TKJ 1"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-1 block font-bold text-slate-700"
                                    >Status Siswa *</label
                                >
                                <select
                                    v-model="form.status_siswa"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 font-bold"
                                >
                                    <option value="AKTIF">AKTIF</option>
                                    <option value="TAMAT">TAMAT / LULUS</option>
                                    <option value="KELUAR">
                                        PINDAH / KELUAR
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Biodata Detail -->
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-4">
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Tempat Lahir</label
                            >
                            <input
                                v-model="form.tempat_lahir"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Tanggal Lahir</label
                            >
                            <input
                                v-model="form.tanggal_lahir"
                                type="date"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Agama</label
                            >
                            <select
                                v-model="form.agama"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            >
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Warga Negara</label
                            >
                            <input
                                v-model="form.warga_negara"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div class="sm:col-span-4">
                            <label class="mb-1 block font-bold text-slate-700"
                                >Alamat Tempat Tinggal Siswa</label
                            >
                            <textarea
                                v-model="form.alamat_siswa"
                                rows="2"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Seksi 2: Data Orang Tua -->
                <div>
                    <h4
                        class="mb-3 border-b border-blue-100 pb-2 text-xs font-bold tracking-wide text-blue-900 uppercase"
                    >
                        2. Data Orang Tua / Wali
                    </h4>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Nama Orang Tua / Wali</label
                            >
                            <input
                                v-model="form.nama_orang_tua"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Pekerjaan Orang Tua</label
                            >
                            <input
                                v-model="form.pekerjaan_orang_tua"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block font-bold text-slate-700"
                                >Alamat Orang Tua / Rumah</label
                            >
                            <textarea
                                v-model="form.alamat_orang_tua"
                                rows="2"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Seksi 3: Diterima, Keluar & Tamat -->
                <div>
                    <h4
                        class="mb-3 border-b border-blue-100 pb-2 text-xs font-bold tracking-wide text-blue-900 uppercase"
                    >
                        3. Riwayat Masuk, Keluar & Tamat
                    </h4>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Diterima Mulai Tgl</label
                            >
                            <input
                                v-model="form.diterima_tanggal"
                                type="date"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block font-bold text-slate-700"
                                >Diterima Asal Sekolah</label
                            >
                            <input
                                v-model="form.diterima_asal_sekolah"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Nomor STTB SMP</label
                            >
                            <input
                                v-model="form.diterima_nomor_sttb"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                    </div>

                    <div
                        class="mt-3 grid grid-cols-1 gap-3 border-t border-slate-100 pt-3 sm:grid-cols-3"
                    >
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Meninggalkan Sekolah (Tgl)</label
                            >
                            <input
                                v-model="form.keluar_tanggal"
                                type="date"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block font-bold text-slate-700"
                                >Alasan Keluar / Pindah</label
                            >
                            <input
                                v-model="form.keluar_alasan"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                    </div>

                    <div
                        class="mt-3 grid grid-cols-1 gap-3 border-t border-slate-100 pt-3 sm:grid-cols-3"
                    >
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Nomor Ijazah/STTB SMK (Tamat)</label
                            >
                            <input
                                v-model="form.tamat_nomor_sttb"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Tanggal Kelulusan/STTB</label
                            >
                            <input
                                v-model="form.tamat_tanggal_sttb"
                                type="date"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-bold text-slate-700"
                                >Keterangan Lain-Lain</label
                            >
                            <input
                                v-model="form.keterangan_lain_lain"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
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
                        @click="closeModal"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="cursor-pointer rounded-lg bg-blue-600 px-5 py-2 font-bold text-white shadow-md transition-all hover:bg-blue-700 active:scale-95 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Menyimpan...</span>
                        <span v-else>{{
                            isEditing ? 'Simpan Perubahan' : 'Tambahkan Siswa'
                        }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
