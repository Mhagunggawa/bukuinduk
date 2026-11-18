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
        }
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
    form.konsentrasi_keahlian_id = item.konsentrasi_keahlian_id ? String(item.konsentrasi_keahlian_id) : '';
    form.kelas_saat_ini = item.kelas_saat_ini || '';
    form.tempat_lahir = item.tempat_lahir || '';
    form.tanggal_lahir = item.tanggal_lahir ? item.tanggal_lahir.substring(0, 10) : '';
    form.warga_negara = item.warga_negara || 'WNI';
    form.agama = item.agama || 'Islam';
    form.alamat_siswa = item.alamat_siswa || '';
    form.nama_orang_tua = item.nama_orang_tua || '';
    form.pekerjaan_orang_tua = item.pekerjaan_orang_tua || '';
    form.alamat_orang_tua = item.alamat_orang_tua || '';
    form.diterima_tanggal = item.diterima_tanggal ? item.diterima_tanggal.substring(0, 10) : '';
    form.diterima_asal_sekolah = item.diterima_asal_sekolah || '';
    form.diterima_nomor_sttb = item.diterima_nomor_sttb || '';
    form.diterima_tanggal_sttb = item.diterima_tanggal_sttb ? item.diterima_tanggal_sttb.substring(0, 10) : '';
    form.keluar_tanggal = item.keluar_tanggal ? item.keluar_tanggal.substring(0, 10) : '';
    form.keluar_alasan = item.keluar_alasan || '';
    form.tamat_nomor_sttb = item.tamat_nomor_sttb || '';
    form.tamat_tanggal_sttb = item.tamat_tanggal_sttb ? item.tamat_tanggal_sttb.substring(0, 10) : '';
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
    if (confirm(`Apakah Anda yakin ingin menghapus data siswa ${item.nama_lengkap} (NISN: ${item.nisn})? Data nilai dan riwayat buku induk siswa ini akan ikut terhapus.`)) {
        router.delete(`/siswa/${item.id}`);
    }
};
</script>

<template>
    <Head title="Direktori Siswa" />

        <div class="space-y-5">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
                <div>
                    <h1 class="text-xl font-black text-blue-950 uppercase tracking-tight flex items-center gap-2">
                        <Users class="w-6 h-6 text-blue-600" />
                        Direktori Siswa
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Pencarian cepat, pengelolaan biodata siswa, dan akses langsung ke Lembar Hasil Prestasi Buku Induk.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <Link
                        href="/import"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-blue-600 text-blue-600 hover:bg-blue-50 text-xs font-bold transition-colors shadow-2xs"
                    >
                        <FileSpreadsheet class="w-4 h-4" />
                        <span>Import Excel</span>
                    </Link>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-xs active:scale-95 cursor-pointer"
                        @click="openCreateModal"
                    >
                        <Plus class="w-4 h-4" />
                        <span>+ Tambah Siswa</span>
                    </button>
                </div>
            </div>

            <!-- Filter & Search Controls Bar -->
            <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Search Input -->
                    <div class="relative">
                        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari NIS, NISN, atau Nama Siswa..."
                            class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400 bg-slate-50/50"
                        />
                    </div>

                    <!-- Filter Jurusan -->
                    <div>
                        <select
                            v-model="jurusanId"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-slate-50/50"
                        >
                            <option value="">Semua Konsentrasi Keahlian</option>
                            <option
                                v-for="jurusan in jurusanList"
                                :key="jurusan.id"
                                :value="jurusan.id"
                            >
                                {{ jurusan.nama_konsentrasi }} ({{ jurusan.kode_singkat }})
                            </option>
                        </select>
                    </div>

                    <!-- Filter Kelas -->
                    <div>
                        <select
                            v-model="kelas"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-slate-50/50"
                        >
                            <option value="">Semua Rombel / Kelas</option>
                            <option
                                v-for="k in kelasList"
                                :key="k"
                                :value="k"
                            >
                                {{ k }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div>
                        <select
                            v-model="status"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-slate-50/50"
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
                    class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs text-slate-500"
                >
                    <span class="flex items-center gap-1.5 font-medium">
                        <Filter class="w-3.5 h-3.5 text-blue-600" />
                        Menampilkan hasil filter (Total: <span class="font-bold text-slate-800">{{ siswa.total }}</span> siswa)
                    </span>
                    <button
                        type="button"
                        class="text-blue-600 hover:text-blue-800 font-semibold hover:underline"
                        @click="resetFilters"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>

            <!-- Table Siswa -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <!-- Header Tabel Utama: bg-blue-900 text-white -->
                        <thead class="bg-blue-900 text-white uppercase text-[11px] font-bold tracking-wider select-none">
                            <tr>
                                <th class="px-3 py-3 w-12 text-center">Foto</th>
                                <th class="px-3 py-3 whitespace-nowrap">NIS / NISN</th>
                                <th class="px-3 py-3">Nama Lengkap</th>
                                <th class="px-3 py-3 w-10 text-center">L/P</th>
                                <th class="px-3 py-3 whitespace-nowrap">Kelas</th>
                                <th class="px-3 py-3 whitespace-nowrap">Konsentrasi Keahlian</th>
                                <th class="px-3 py-3 text-center whitespace-nowrap">Status</th>
                                <th class="px-3 py-3 text-center w-40 whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr
                                v-for="item in siswa.data"
                                :key="item.id"
                                class="hover:bg-blue-50/60 transition-colors"
                            >
                                <!-- Pasfoto mini -->
                                <td class="px-3 py-2.5 text-center">
                                    <div class="w-9 h-12 bg-slate-100 border border-slate-300 rounded overflow-hidden mx-auto flex items-center justify-center text-slate-400 shadow-2xs">
                                        <img
                                            v-if="item.pasfoto_url"
                                            :src="item.pasfoto_url"
                                            :alt="item.nama_lengkap"
                                            class="w-full h-full object-cover"
                                        />
                                        <UserCheck v-else class="w-5 h-5 text-slate-300" />
                                    </div>
                                </td>

                                <!-- NIS / NISN -->
                                <td class="px-3 py-2.5 whitespace-nowrap font-mono text-[11px]">
                                    <div class="font-bold text-slate-800">{{ item.nis }}</div>
                                    <div class="text-slate-500 font-medium">{{ item.nisn }}</div>
                                </td>

                                <!-- Nama Lengkap -->
                                <td class="px-3 py-2.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-900 hover:text-blue-600 transition-colors">
                                        <Link :href="`/siswa/${item.id}/buku-induk`">
                                            {{ item.nama_lengkap }}
                                        </Link>
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        {{ item.tempat_lahir || '-' }}, {{ item.tanggal_lahir ? item.tanggal_lahir.substring(0, 10) : '-' }}
                                    </div>
                                </td>

                                <!-- Jenis Kelamin -->
                                <td class="px-3 py-2.5 text-center whitespace-nowrap font-semibold">
                                    <span
                                        :class="item.jenis_kelamin === 'L' ? 'text-blue-700 bg-blue-50' : 'text-rose-700 bg-rose-50'"
                                        class="px-1.5 py-0.5 rounded text-[10px] border border-current font-bold"
                                    >
                                        {{ item.jenis_kelamin }}
                                    </span>
                                </td>

                                <!-- Kelas -->
                                <td class="px-3 py-2.5 whitespace-nowrap font-semibold text-slate-700">
                                    {{ item.kelas_saat_ini || '-' }}
                                </td>

                                <!-- Konsentrasi Keahlian -->
                                <td class="px-3 py-2.5 whitespace-nowrap">
                                    <div v-if="item.konsentrasi_keahlian" class="font-medium text-slate-800">
                                        {{ item.konsentrasi_keahlian.nama_konsentrasi }}
                                        <span class="text-blue-600 font-bold">({{ item.konsentrasi_keahlian.kode_singkat }})</span>
                                    </div>
                                    <span v-else class="text-slate-400 italic">Belum ditentukan</span>
                                </td>

                                <!-- Status Siswa -->
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    <span
                                        v-if="item.status_siswa === 'AKTIF'"
                                        class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300"
                                    >
                                        AKTIF
                                    </span>
                                    <span
                                        v-else-if="item.status_siswa === 'TAMAT'"
                                        class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300"
                                    >
                                        TAMAT
                                    </span>
                                    <span
                                        v-else
                                        class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300"
                                    >
                                        KELUAR
                                    </span>
                                </td>

                                <!-- Tombol Aksi -->
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Buka Buku Induk -->
                                        <Link
                                            :href="`/siswa/${item.id}/buku-induk`"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] shadow-2xs transition-colors"
                                            title="Buka Lembar Buku Induk"
                                        >
                                            <BookOpen class="w-3.5 h-3.5" />
                                            <span>Buku Induk</span>
                                        </Link>

                                        <!-- Edit -->
                                        <button
                                            type="button"
                                            class="p-1 rounded text-slate-600 hover:text-blue-700 hover:bg-blue-100 transition-colors cursor-pointer"
                                            title="Edit Biodata Siswa"
                                            @click="openEditModal(item)"
                                        >
                                            <Edit3 class="w-3.5 h-3.5" />
                                        </button>

                                        <!-- Hapus -->
                                        <button
                                            type="button"
                                            class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Hapus Siswa"
                                            @click="deleteSiswa(item)"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="siswa.data.length === 0">
                                <td colspan="8" class="text-center py-10 text-slate-400">
                                    Tidak ada data siswa yang sesuai dengan kriteria pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
                    <div>
                        Menampilkan <span class="font-bold text-slate-900">{{ siswa.from || 0 }}</span> sampai <span class="font-bold text-slate-900">{{ siswa.to || 0 }}</span> dari total <span class="font-bold text-slate-900">{{ siswa.total }}</span> siswa
                    </div>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in siswa.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'px-2.5 py-1.5 rounded text-xs font-semibold transition-colors',
                                    link.active
                                        ? 'bg-blue-600 text-white shadow-2xs'
                                        : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100',
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-2 py-1.5 text-slate-400 text-xs"
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
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div class="bg-white rounded-2xl max-w-4xl w-full shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-blue-900 text-white flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-base uppercase tracking-wide">
                            {{ isEditing ? 'Edit Data Siswa' : 'Tambah Siswa Baru' }}
                        </h3>
                        <p class="text-xs text-blue-200">
                            Isi informasi identitas lengkap siswa untuk dicatat pada Buku Induk.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="text-blue-200 hover:text-white p-1 rounded-lg"
                        @click="closeModal"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Modal Body (Form) -->
                <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto p-6 space-y-6 text-xs">
                    <!-- Seksi 1: Identitas Pokok & Pasfoto -->
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wide text-blue-900 border-b border-blue-100 pb-2 mb-3">
                            1. Identitas Pokok Siswa
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Pasfoto Upload -->
                            <div class="flex flex-col items-center justify-center p-3 bg-slate-50 border border-dashed border-slate-300 rounded-xl text-center">
                                <div class="w-24 h-32 bg-white border border-slate-200 rounded flex items-center justify-center overflow-hidden mb-2 shadow-2xs">
                                    <img
                                        v-if="form.pasfoto"
                                        :src="URL.createObjectURL(form.pasfoto)"
                                        class="w-full h-full object-cover"
                                    />
                                    <Camera v-else class="w-8 h-8 text-slate-300" />
                                </div>
                                <label class="cursor-pointer text-[11px] font-bold text-blue-600 hover:text-blue-800">
                                    <span>Pilih Pasfoto 3x4</span>
                                    <input type="file" accept="image/*" class="hidden" @change="handlePhotoChange" />
                                </label>
                                <span class="text-[10px] text-slate-400 mt-0.5">JPG / PNG, maks 2MB</span>
                            </div>

                            <!-- NIS, NISN, Nama, JK, Jurusan, Kelas -->
                            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">NIS (Wajib) *</label>
                                    <input
                                        v-model="form.nis"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="form.errors.nis ? 'border-rose-500 bg-rose-50' : 'border-slate-300'"
                                    />
                                    <span v-if="form.errors.nis" class="text-[10px] text-rose-600">{{ form.errors.nis }}</span>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">NISN (Wajib, 10 Digit) *</label>
                                    <input
                                        v-model="form.nisn"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="form.errors.nisn ? 'border-rose-500 bg-rose-50' : 'border-slate-300'"
                                    />
                                    <span v-if="form.errors.nisn" class="text-[10px] text-rose-600">{{ form.errors.nisn }}</span>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Siswa *</label>
                                    <input
                                        v-model="form.nama_lengkap"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 uppercase"
                                        :class="form.errors.nama_lengkap ? 'border-rose-500 bg-rose-50' : 'border-slate-300'"
                                    />
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Jenis Kelamin *</label>
                                    <select v-model="form.jenis_kelamin" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                                        <option value="L">Laki-Laki (L)</option>
                                        <option value="P">Perempuan (P)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Konsentrasi Keahlian (Jurusan)</label>
                                    <select v-model="form.konsentrasi_keahlian_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                                        <option value="">-- Pilih Jurusan --</option>
                                        <option v-for="j in jurusanList" :key="j.id" :value="j.id">
                                            {{ j.nama_konsentrasi }} ({{ j.kode_singkat }})
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Rombel / Kelas Saat Ini</label>
                                    <input v-model="form.kelas_saat_ini" type="text" placeholder="Contoh: X TKJ 1" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Status Siswa *</label>
                                    <select v-model="form.status_siswa" class="w-full px-3 py-2 border border-slate-300 rounded-lg font-bold">
                                        <option value="AKTIF">AKTIF</option>
                                        <option value="TAMAT">TAMAT / LULUS</option>
                                        <option value="KELUAR">PINDAH / KELUAR</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Biodata Detail -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Tempat Lahir</label>
                                <input v-model="form.tempat_lahir" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Tanggal Lahir</label>
                                <input v-model="form.tanggal_lahir" type="date" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Agama</label>
                                <select v-model="form.agama" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Warga Negara</label>
                                <input v-model="form.warga_negara" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block font-bold text-slate-700 mb-1">Alamat Tempat Tinggal Siswa</label>
                                <textarea v-model="form.alamat_siswa" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Seksi 2: Data Orang Tua -->
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wide text-blue-900 border-b border-blue-100 pb-2 mb-3">
                            2. Data Orang Tua / Wali
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Nama Orang Tua / Wali</label>
                                <input v-model="form.nama_orang_tua" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Pekerjaan Orang Tua</label>
                                <input v-model="form.pekerjaan_orang_tua" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1">Alamat Orang Tua / Rumah</label>
                                <textarea v-model="form.alamat_orang_tua" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Seksi 3: Diterima, Keluar & Tamat -->
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-wide text-blue-900 border-b border-blue-100 pb-2 mb-3">
                            3. Riwayat Masuk, Keluar & Tamat
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Diterima Mulai Tgl</label>
                                <input v-model="form.diterima_tanggal" type="date" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1">Diterima Asal Sekolah</label>
                                <input v-model="form.diterima_asal_sekolah" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Nomor STTB SMP</label>
                                <input v-model="form.diterima_nomor_sttb" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3 pt-3 border-t border-slate-100">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Meninggalkan Sekolah (Tgl)</label>
                                <input v-model="form.keluar_tanggal" type="date" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1">Alasan Keluar / Pindah</label>
                                <input v-model="form.keluar_alasan" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3 pt-3 border-t border-slate-100">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Nomor Ijazah/STTB SMK (Tamat)</label>
                                <input v-model="form.tamat_nomor_sttb" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Tanggal Kelulusan/STTB</label>
                                <input v-model="form.tamat_tanggal_sttb" type="date" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Keterangan Lain-Lain</label>
                                <input v-model="form.keterangan_lain_lain" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3 sticky bottom-0 bg-white">
                        <button
                            type="button"
                            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold transition-colors cursor-pointer"
                            @click="closeModal"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold transition-all shadow-md active:scale-95 disabled:opacity-50 cursor-pointer"
                        >
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>{{ isEditing ? 'Simpan Perubahan' : 'Tambahkan Siswa' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
</template>
