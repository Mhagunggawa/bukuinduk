<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Database,
    Plus,
    Edit3,
    Trash2,
    CheckCircle2,
    Calendar,
    Layers,
    BookOpen,
    Activity,
    X,
    Star,
} from '@lucide/vue';

interface BidangStudi {
    id: number;
    kode: string;
    nama_bidang_studi: string;
}

interface ProgramKeahlian {
    id: number;
    bidang_studi_id: number;
    kode: string;
    nama_program_keahlian: string;
    bidang_studi?: BidangStudi;
}

interface KonsentrasiKeahlian {
    id: number;
    program_keahlian_id: number;
    kode_singkat: string;
    nama_konsentrasi: string;
    program_keahlian?: ProgramKeahlian;
}

interface MataPelajaran {
    id: number;
    kode_mapel: string;
    nama_mapel: string;
    kelompok: 'A_UMUM' | 'B_KEJURUAN';
    sub_kelompok: string | null;
    no_urut: number;
}

interface Ekstrakurikuler {
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

const props = defineProps<{
    bidangList: BidangStudi[];
    programList: ProgramKeahlian[];
    jurusanList: KonsentrasiKeahlian[];
    mapelList: MataPelajaran[];
    ekskulList: Ekstrakurikuler[];
    tahunList: TahunPelajaran[];
}>();

// Active Section Tab
const activeTab = ref<'jurusan' | 'mapel' | 'ekskul' | 'tahun'>('jurusan');

// --- Modal Jurusan ---
const isJurusanModalOpen = ref(false);
const editingJurusanId = ref<number | null>(null);
const jurusanForm = useForm({
    program_keahlian_id: '',
    kode_singkat: '',
    nama_konsentrasi: '',
});

const openCreateJurusan = () => {
    editingJurusanId.value = null;
    jurusanForm.reset();
    jurusanForm.clearErrors();
    if (props.programList.length > 0) {
        jurusanForm.program_keahlian_id = String(props.programList[0].id);
    }
    isJurusanModalOpen.value = true;
};

const openEditJurusan = (j: KonsentrasiKeahlian) => {
    editingJurusanId.value = j.id;
    jurusanForm.reset();
    jurusanForm.clearErrors();
    jurusanForm.program_keahlian_id = String(j.program_keahlian_id);
    jurusanForm.kode_singkat = j.kode_singkat;
    jurusanForm.nama_konsentrasi = j.nama_konsentrasi;
    isJurusanModalOpen.value = true;
};

const submitJurusan = () => {
    if (editingJurusanId.value) {
        jurusanForm.put(`/master-data/jurusan/${editingJurusanId.value}`, {
            onSuccess: () => {
                isJurusanModalOpen.value = false;
            },
        });
    } else {
        jurusanForm.post('/master-data/jurusan', {
            onSuccess: () => {
                isJurusanModalOpen.value = false;
            },
        });
    }
};

const deleteJurusan = (j: KonsentrasiKeahlian) => {
    if (confirm(`Hapus jurusan ${j.nama_konsentrasi} (${j.kode_singkat})?`)) {
        router.delete(`/master-data/jurusan/${j.id}`);
    }
};

// --- Modal Mata Pelajaran ---
const isMapelModalOpen = ref(false);
const editingMapelId = ref<number | null>(null);
const mapelForm = useForm({
    kode_mapel: '',
    nama_mapel: '',
    kelompok: 'A_UMUM' as 'A_UMUM' | 'B_KEJURUAN',
    sub_kelompok: '',
    no_urut: 1,
});

const openCreateMapel = () => {
    editingMapelId.value = null;
    mapelForm.reset();
    mapelForm.clearErrors();
    mapelForm.kelompok = 'A_UMUM';
    mapelForm.no_urut = props.mapelList.length + 1;
    isMapelModalOpen.value = true;
};

const openEditMapel = (m: MataPelajaran) => {
    editingMapelId.value = m.id;
    mapelForm.reset();
    mapelForm.clearErrors();
    mapelForm.kode_mapel = m.kode_mapel;
    mapelForm.nama_mapel = m.nama_mapel;
    mapelForm.kelompok = m.kelompok;
    mapelForm.sub_kelompok = m.sub_kelompok || '';
    mapelForm.no_urut = m.no_urut;
    isMapelModalOpen.value = true;
};

const submitMapel = () => {
    if (editingMapelId.value) {
        mapelForm.put(`/master-data/mapel/${editingMapelId.value}`, {
            onSuccess: () => {
                isMapelModalOpen.value = false;
            },
        });
    } else {
        mapelForm.post('/master-data/mapel', {
            onSuccess: () => {
                isMapelModalOpen.value = false;
            },
        });
    }
};

const deleteMapel = (m: MataPelajaran) => {
    if (confirm(`Hapus mata pelajaran ${m.nama_mapel} (${m.kode_mapel})?`)) {
        router.delete(`/master-data/mapel/${m.id}`);
    }
};

// --- Modal Ekstrakurikuler ---
const isEkskulModalOpen = ref(false);
const editingEkskulId = ref<number | null>(null);
const ekskulForm = useForm({
    kode_ekskul: '',
    nama_ekskul: '',
    no_urut: 1,
});

const openCreateEkskul = () => {
    editingEkskulId.value = null;
    ekskulForm.reset();
    ekskulForm.clearErrors();
    ekskulForm.no_urut = props.ekskulList.length + 1;
    isEkskulModalOpen.value = true;
};

const openEditEkskul = (e: Ekstrakurikuler) => {
    editingEkskulId.value = e.id;
    ekskulForm.reset();
    ekskulForm.clearErrors();
    ekskulForm.kode_ekskul = e.kode_ekskul;
    ekskulForm.nama_ekskul = e.nama_ekskul;
    ekskulForm.no_urut = e.no_urut;
    isEkskulModalOpen.value = true;
};

const submitEkskul = () => {
    if (editingEkskulId.value) {
        ekskulForm.put(`/master-data/ekskul/${editingEkskulId.value}`, {
            onSuccess: () => {
                isEkskulModalOpen.value = false;
            },
        });
    } else {
        ekskulForm.post('/master-data/ekskul', {
            onSuccess: () => {
                isEkskulModalOpen.value = false;
            },
        });
    }
};

const deleteEkskul = (e: Ekstrakurikuler) => {
    if (confirm(`Hapus ekstrakurikuler ${e.nama_ekskul}?`)) {
        router.delete(`/master-data/ekskul/${e.id}`);
    }
};

// --- Modal Tahun Pelajaran ---
const isTahunModalOpen = ref(false);
const editingTahunId = ref<number | null>(null);
const tahunForm = useForm({
    tahun_pelajaran: '',
    is_aktif: false,
});

const openCreateTahun = () => {
    editingTahunId.value = null;
    tahunForm.reset();
    tahunForm.clearErrors();
    tahunForm.is_aktif = false;
    isTahunModalOpen.value = true;
};

const openEditTahun = (t: TahunPelajaran) => {
    editingTahunId.value = t.id;
    tahunForm.reset();
    tahunForm.clearErrors();
    tahunForm.tahun_pelajaran = t.tahun_pelajaran;
    tahunForm.is_aktif = t.is_aktif;
    isTahunModalOpen.value = true;
};

const submitTahun = () => {
    if (editingTahunId.value) {
        tahunForm.put(`/master-data/tahun/${editingTahunId.value}`, {
            onSuccess: () => {
                isTahunModalOpen.value = false;
            },
        });
    } else {
        tahunForm.post('/master-data/tahun', {
            onSuccess: () => {
                isTahunModalOpen.value = false;
            },
        });
    }
};

const deleteTahun = (t: TahunPelajaran) => {
    if (confirm(`Hapus tahun pelajaran ${t.tahun_pelajaran}?`)) {
        router.delete(`/master-data/tahun/${t.id}`);
    }
};

const setTahunAktif = (t: TahunPelajaran) => {
    router.patch(`/master-data/tahun/${t.id}/aktif`);
};
</script>

<template>
    <Head title="Manajemen Data Master" />
    <div class="mx-auto max-w-6xl space-y-6">
        <!-- Header Section -->
        <div
            class="flex flex-col justify-between gap-4 rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs sm:flex-row sm:items-center"
        >
            <div>
                <h1
                    class="flex items-center gap-2 text-xl font-black tracking-tight text-blue-950 uppercase"
                >
                    <Database class="h-6 w-6 text-amber-500" />
                    Manajemen Data Master Akademik
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    Kelola data referensi utama sistem: Jurusan (Konsentrasi
                    Keahlian), Mata Pelajaran, Ekstrakurikuler, dan Tahun
                    Pelajaran.
                </p>
            </div>
        </div>

        <!-- Tabs Navigasi Master Data -->
        <div
            class="flex flex-wrap gap-2 rounded-xl bg-slate-200/60 p-1.5 text-xs font-bold sm:flex-nowrap"
        >
            <button
                type="button"
                :class="[
                    'flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg px-4 py-2.5 transition-all',
                    activeTab === 'jurusan'
                        ? 'bg-blue-600 text-white shadow-xs'
                        : 'text-slate-700 hover:bg-white/60',
                ]"
                @click="activeTab = 'jurusan'"
            >
                <Layers class="h-4 w-4" />
                <span>Konsentrasi Keahlian (Jurusan)</span>
            </button>

            <button
                type="button"
                :class="[
                    'flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg px-4 py-2.5 transition-all',
                    activeTab === 'mapel'
                        ? 'bg-blue-600 text-white shadow-xs'
                        : 'text-slate-700 hover:bg-white/60',
                ]"
                @click="activeTab = 'mapel'"
            >
                <BookOpen class="h-4 w-4" />
                <span>Mata Pelajaran (Mapel)</span>
            </button>

            <button
                type="button"
                :class="[
                    'flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg px-4 py-2.5 transition-all',
                    activeTab === 'ekskul'
                        ? 'bg-blue-600 text-white shadow-xs'
                        : 'text-slate-700 hover:bg-white/60',
                ]"
                @click="activeTab = 'ekskul'"
            >
                <Activity class="h-4 w-4" />
                <span>Ekstrakurikuler</span>
            </button>

            <button
                type="button"
                :class="[
                    'flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg px-4 py-2.5 transition-all',
                    activeTab === 'tahun'
                        ? 'bg-blue-600 text-white shadow-xs'
                        : 'text-slate-700 hover:bg-white/60',
                ]"
                @click="activeTab = 'tahun'"
            >
                <Calendar class="h-4 w-4" />
                <span>Tahun Pelajaran</span>
            </button>
        </div>

        <!-- ================= TAB 1: KONSENTRASI KEAHLIAN (JURUSAN) ================= -->
        <div
            v-if="activeTab === 'jurusan'"
            class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs"
        >
            <div
                class="flex items-center justify-between border-b border-slate-200 p-4 sm:p-5"
            >
                <div>
                    <h2
                        class="text-sm font-bold tracking-wide text-slate-900 uppercase"
                    >
                        Daftar Konsentrasi Keahlian (Jurusan)
                    </h2>
                    <p class="text-xs text-slate-500">
                        Kunci pencocokan kolom
                        <strong>kode_jurusan</strong> pada Excel.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-blue-700"
                    @click="openCreateJurusan"
                >
                    <Plus class="h-4 w-4" />
                    <span>+ Tambah Jurusan</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead
                        class="bg-blue-900 text-[11px] font-bold text-white uppercase"
                    >
                        <tr>
                            <th class="w-12 px-4 py-3 text-center">No</th>
                            <th class="w-32 px-4 py-3">Kode Singkat</th>
                            <th class="px-4 py-3">Nama Konsentrasi Keahlian</th>
                            <th class="px-4 py-3">Program & Bidang Keahlian</th>
                            <th class="w-28 px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr
                            v-for="(j, idx) in jurusanList"
                            :key="j.id"
                            class="hover:bg-blue-50/50"
                        >
                            <td
                                class="px-4 py-2.5 text-center font-bold text-slate-500"
                            >
                                {{ idx + 1 }}
                            </td>
                            <td
                                class="px-4 py-2.5 font-mono font-bold text-blue-900"
                            >
                                {{ j.kode_singkat }}
                            </td>
                            <td class="px-4 py-2.5 font-bold text-slate-900">
                                {{ j.nama_konsentrasi }}
                            </td>
                            <td class="px-4 py-2.5 text-slate-600">
                                {{ j.program_keahlian?.nama_program_keahlian }}
                                <span class="block text-[10px] text-slate-400">
                                    Bidang:
                                    {{
                                        j.program_keahlian?.bidang_studi
                                            ?.nama_bidang_studi
                                    }}
                                </span>
                            </td>
                            <td
                                class="px-4 py-2.5 text-right whitespace-nowrap"
                            >
                                <button
                                    type="button"
                                    class="mr-1 rounded p-1 text-slate-600 hover:bg-blue-50 hover:text-blue-700"
                                    title="Edit"
                                    @click="openEditJurusan(j)"
                                >
                                    <Edit3 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-1 text-slate-400 hover:bg-rose-50 hover:text-rose-600"
                                    title="Hapus"
                                    @click="deleteJurusan(j)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= TAB 2: MATA PELAJARAN ================= -->
        <div
            v-if="activeTab === 'mapel'"
            class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs"
        >
            <div
                class="flex items-center justify-between border-b border-slate-200 p-4 sm:p-5"
            >
                <div>
                    <h2
                        class="text-sm font-bold tracking-wide text-slate-900 uppercase"
                    >
                        Daftar Mata Pelajaran (Umum & Kejuruan)
                    </h2>
                    <p class="text-xs text-slate-500">
                        Mata pelajaran yang ditampilkan pada lembar Buku Induk
                        matriks nilai.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-blue-700"
                    @click="openCreateMapel"
                >
                    <Plus class="h-4 w-4" />
                    <span>+ Tambah Mata Pelajaran</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead
                        class="bg-blue-900 text-[11px] font-bold text-white uppercase"
                    >
                        <tr>
                            <th class="w-16 px-4 py-3 text-center">No Urut</th>
                            <th class="w-32 px-4 py-3">Kode Mapel</th>
                            <th class="px-4 py-3">Nama Mata Pelajaran</th>
                            <th class="w-40 px-4 py-3">Kelompok</th>
                            <th class="px-4 py-3">Sub-Kelompok</th>
                            <th class="w-28 px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr
                            v-for="m in mapelList"
                            :key="m.id"
                            class="hover:bg-blue-50/50"
                        >
                            <td
                                class="px-4 py-2.5 text-center font-bold text-slate-600"
                            >
                                {{ m.no_urut }}
                            </td>
                            <td
                                class="px-4 py-2.5 font-mono font-bold text-blue-900"
                            >
                                {{ m.kode_mapel }}
                            </td>
                            <td class="px-4 py-2.5 font-bold text-slate-900">
                                {{ m.nama_mapel }}
                            </td>
                            <td class="px-4 py-2.5">
                                <span
                                    v-if="m.kelompok === 'A_UMUM'"
                                    class="rounded bg-sky-100 px-2 py-0.5 text-[10.5px] font-bold text-sky-800"
                                >
                                    A. UMUM
                                </span>
                                <span
                                    v-else
                                    class="rounded bg-indigo-100 px-2 py-0.5 text-[10.5px] font-bold text-indigo-800"
                                >
                                    B. KEJURUAN
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-500">
                                {{ m.sub_kelompok || '-' }}
                            </td>
                            <td
                                class="px-4 py-2.5 text-right whitespace-nowrap"
                            >
                                <button
                                    type="button"
                                    class="mr-1 rounded p-1 text-slate-600 hover:bg-blue-50 hover:text-blue-700"
                                    title="Edit"
                                    @click="openEditMapel(m)"
                                >
                                    <Edit3 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-1 text-slate-400 hover:bg-rose-50 hover:text-rose-600"
                                    title="Hapus"
                                    @click="deleteMapel(m)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= TAB 3: EKSTRAKURIKULER ================= -->
        <div
            v-if="activeTab === 'ekskul'"
            class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs"
        >
            <div
                class="flex items-center justify-between border-b border-slate-200 p-4 sm:p-5"
            >
                <div>
                    <h2
                        class="text-sm font-bold tracking-wide text-slate-900 uppercase"
                    >
                        Daftar Kegiatan Ekstrakurikuler
                    </h2>
                    <p class="text-xs text-slate-500">
                        Kegiatan non-akademik yang tercantum pada lembar Buku
                        Induk.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-blue-700"
                    @click="openCreateEkskul"
                >
                    <Plus class="h-4 w-4" />
                    <span>+ Tambah Ekstrakurikuler</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead
                        class="bg-blue-900 text-[11px] font-bold text-white uppercase"
                    >
                        <tr>
                            <th class="w-16 px-4 py-3 text-center">No Urut</th>
                            <th class="w-48 px-4 py-3">Kode Ekskul</th>
                            <th class="px-4 py-3">Nama Ekstrakurikuler</th>
                            <th class="w-28 px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr
                            v-for="e in ekskulList"
                            :key="e.id"
                            class="hover:bg-blue-50/50"
                        >
                            <td
                                class="px-4 py-2.5 text-center font-bold text-slate-600"
                            >
                                {{ e.no_urut }}
                            </td>
                            <td
                                class="px-4 py-2.5 font-mono font-bold text-blue-900"
                            >
                                {{ e.kode_ekskul }}
                            </td>
                            <td class="px-4 py-2.5 font-bold text-slate-900">
                                {{ e.nama_ekskul }}
                            </td>
                            <td
                                class="px-4 py-2.5 text-right whitespace-nowrap"
                            >
                                <button
                                    type="button"
                                    class="mr-1 rounded p-1 text-slate-600 hover:bg-blue-50 hover:text-blue-700"
                                    title="Edit"
                                    @click="openEditEkskul(e)"
                                >
                                    <Edit3 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-1 text-slate-400 hover:bg-rose-50 hover:text-rose-600"
                                    title="Hapus"
                                    @click="deleteEkskul(e)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= TAB 4: TAHUN PELAJARAN ================= -->
        <div
            v-if="activeTab === 'tahun'"
            class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs"
        >
            <div
                class="flex items-center justify-between border-b border-slate-200 p-4 sm:p-5"
            >
                <div>
                    <h2
                        class="text-sm font-bold tracking-wide text-slate-900 uppercase"
                    >
                        Daftar Tahun Pelajaran
                    </h2>
                    <p class="text-xs text-slate-500">
                        Tentukan tahun pelajaran yang sedang aktif berjalan di
                        sekolah.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-blue-700"
                    @click="openCreateTahun"
                >
                    <Plus class="h-4 w-4" />
                    <span>+ Tambah Tahun Pelajaran</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead
                        class="bg-blue-900 text-[11px] font-bold text-white uppercase"
                    >
                        <tr>
                            <th class="w-16 px-4 py-3 text-center">No</th>
                            <th class="px-4 py-3">Tahun Pelajaran</th>
                            <th class="w-36 px-4 py-3 text-center">
                                Status Aktif
                            </th>
                            <th class="w-44 px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr
                            v-for="(t, idx) in tahunList"
                            :key="t.id"
                            class="hover:bg-blue-50/50"
                        >
                            <td
                                class="px-4 py-2.5 text-center font-bold text-slate-500"
                            >
                                {{ idx + 1 }}
                            </td>
                            <td
                                class="px-4 py-2.5 font-mono text-sm font-bold text-slate-900"
                            >
                                {{ t.tahun_pelajaran }}
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <span
                                    v-if="t.is_aktif"
                                    class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    Aktif Berjalan
                                </span>
                                <span v-else class="text-xs text-slate-400"
                                    >Tidak Aktif</span
                                >
                            </td>
                            <td
                                class="px-4 py-2.5 text-right whitespace-nowrap"
                            >
                                <button
                                    v-if="!t.is_aktif"
                                    type="button"
                                    class="mr-2 cursor-pointer rounded border border-blue-200 bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-blue-700 transition-colors hover:bg-blue-600 hover:text-white"
                                    title="Jadikan Tahun Aktif"
                                    @click="setTahunAktif(t)"
                                >
                                    Set Aktif
                                </button>
                                <button
                                    type="button"
                                    class="mr-1 rounded p-1 text-slate-600 hover:bg-blue-50 hover:text-blue-700"
                                    title="Edit"
                                    @click="openEditTahun(t)"
                                >
                                    <Edit3 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-1 text-slate-400 hover:bg-rose-50 hover:text-rose-600"
                                    title="Hapus"
                                    @click="deleteTahun(t)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= MODAL JURUSAN ================= -->
    <div
        v-if="isJurusanModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs"
    >
        <div
            class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        >
            <div
                class="flex items-center justify-between bg-blue-900 px-6 py-4 text-white"
            >
                <h3 class="text-sm font-black uppercase">
                    {{
                        editingJurusanId
                            ? 'Edit Konsentrasi Keahlian'
                            : 'Tambah Konsentrasi Keahlian'
                    }}
                </h3>
                <button
                    @click="isJurusanModalOpen = false"
                    class="p-1 text-blue-200 hover:text-white"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>
            <form @submit.prevent="submitJurusan" class="space-y-4 p-6 text-xs">
                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Program Keahlian *</label
                    >
                    <select
                        v-model="jurusanForm.program_keahlian_id"
                        required
                        class="w-full rounded-lg border px-3 py-2"
                    >
                        <option
                            v-for="p in programList"
                            :key="p.id"
                            :value="p.id"
                        >
                            {{ p.nama_program_keahlian }} ({{ p.kode }})
                        </option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Kode Singkat (Dipakai di Excel) *</label
                    >
                    <input
                        v-model="jurusanForm.kode_singkat"
                        type="text"
                        required
                        placeholder="Contoh: TKJ"
                        class="w-full rounded-lg border px-3 py-2 font-mono uppercase"
                    />
                </div>
                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Nama Konsentrasi Keahlian *</label
                    >
                    <input
                        v-model="jurusanForm.nama_konsentrasi"
                        type="text"
                        required
                        placeholder="Contoh: TEKNIK KOMPUTER & JARINGAN (TKJ)"
                        class="w-full rounded-lg border px-3 py-2 uppercase"
                    />
                </div>
                <div
                    class="flex justify-end gap-2 border-t border-slate-200 pt-3"
                >
                    <button
                        type="button"
                        @click="isJurusanModalOpen = false"
                        class="rounded-lg border px-4 py-2"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="jurusanForm.processing"
                        class="rounded-lg bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL MATA PELAJARAN ================= -->
    <div
        v-if="isMapelModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs"
    >
        <div
            class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        >
            <div
                class="flex items-center justify-between bg-blue-900 px-6 py-4 text-white"
            >
                <h3 class="text-sm font-black uppercase">
                    {{
                        editingMapelId
                            ? 'Edit Mata Pelajaran'
                            : 'Tambah Mata Pelajaran'
                    }}
                </h3>
                <button
                    @click="isMapelModalOpen = false"
                    class="p-1 text-blue-200 hover:text-white"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>
            <form @submit.prevent="submitMapel" class="space-y-4 p-6 text-xs">
                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Kode Mapel (Dipakai di Excel) *</label
                    >
                    <input
                        v-model="mapelForm.kode_mapel"
                        type="text"
                        required
                        placeholder="Contoh: MTK / INFORMATIKA"
                        class="w-full rounded-lg border px-3 py-2 font-mono uppercase"
                    />
                </div>
                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Nama Mata Pelajaran *</label
                    >
                    <input
                        v-model="mapelForm.nama_mapel"
                        type="text"
                        required
                        placeholder="Contoh: Matematika"
                        class="w-full rounded-lg border px-3 py-2"
                    />
                </div>
                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Kelompok Mapel *</label
                    >
                    <select
                        v-model="mapelForm.kelompok"
                        class="w-full rounded-lg border px-3 py-2 font-bold"
                    >
                        <option value="A_UMUM">
                            A. Kelompok Mata Pelajaran Umum
                        </option>
                        <option value="B_KEJURUAN">
                            B. Kelompok Mata Pelajaran Kejuruan
                        </option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block font-bold text-slate-700"
                            >Sub-Kelompok (Opsional)</label
                        >
                        <input
                            v-model="mapelForm.sub_kelompok"
                            type="text"
                            placeholder="Contoh: Pendidikan Agama / Pilihan"
                            class="w-full rounded-lg border px-3 py-2"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block font-bold text-slate-700"
                            >Nomor Urut Tampil *</label
                        >
                        <input
                            v-model.number="mapelForm.no_urut"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border px-3 py-2 font-bold"
                        />
                    </div>
                </div>
                <div
                    class="flex justify-end gap-2 border-t border-slate-200 pt-3"
                >
                    <button
                        type="button"
                        @click="isMapelModalOpen = false"
                        class="rounded-lg border px-4 py-2"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="mapelForm.processing"
                        class="rounded-lg bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL EKSTRAKURIKULER ================= -->
    <div
        v-if="isEkskulModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs"
    >
        <div
            class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        >
            <div
                class="flex items-center justify-between bg-blue-900 px-6 py-4 text-white"
            >
                <h3 class="text-sm font-black uppercase">
                    {{
                        editingEkskulId
                            ? 'Edit Ekstrakurikuler'
                            : 'Tambah Ekstrakurikuler'
                    }}
                </h3>
                <button
                    @click="isEkskulModalOpen = false"
                    class="p-1 text-blue-200 hover:text-white"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>
            <form @submit.prevent="submitEkskul" class="space-y-4 p-6 text-xs">
                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Kode Ekskul (Dipakai di Excel) *</label
                    >
                    <input
                        v-model="ekskulForm.kode_ekskul"
                        type="text"
                        required
                        placeholder="Contoh: EKSKUL_PRAMUKA"
                        class="w-full rounded-lg border px-3 py-2 font-mono uppercase"
                    />
                </div>
                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Nama Ekstrakurikuler *</label
                    >
                    <input
                        v-model="ekskulForm.nama_ekskul"
                        type="text"
                        required
                        placeholder="Contoh: Pramuka"
                        class="w-full rounded-lg border px-3 py-2"
                    />
                </div>
                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Nomor Urut Tampil *</label
                    >
                    <input
                        v-model.number="ekskulForm.no_urut"
                        type="number"
                        min="1"
                        class="w-full rounded-lg border px-3 py-2 font-bold"
                    />
                </div>
                <div
                    class="flex justify-end gap-2 border-t border-slate-200 pt-3"
                >
                    <button
                        type="button"
                        @click="isEkskulModalOpen = false"
                        class="rounded-lg border px-4 py-2"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="ekskulForm.processing"
                        class="rounded-lg bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL TAHUN PELAJARAN ================= -->
    <div
        v-if="isTahunModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs"
    >
        <div
            class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        >
            <div
                class="flex items-center justify-between bg-blue-900 px-6 py-4 text-white"
            >
                <h3 class="text-sm font-black uppercase">
                    {{
                        editingTahunId
                            ? 'Edit Tahun Pelajaran'
                            : 'Tambah Tahun Pelajaran'
                    }}
                </h3>
                <button
                    @click="isTahunModalOpen = false"
                    class="p-1 text-blue-200 hover:text-white"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>
            <form @submit.prevent="submitTahun" class="space-y-4 p-6 text-xs">
                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Tahun Pelajaran *</label
                    >
                    <input
                        v-model="tahunForm.tahun_pelajaran"
                        type="text"
                        required
                        placeholder="Contoh: 2025/2026"
                        class="w-full rounded-lg border px-3 py-2 font-mono font-bold"
                    />
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <input
                        v-model="tahunForm.is_aktif"
                        type="checkbox"
                        id="checkAktif"
                        class="h-4 w-4 rounded text-blue-600 focus:ring-blue-500"
                    />
                    <label for="checkAktif" class="font-bold text-slate-800"
                        >Set sebagai Tahun Pelajaran Aktif</label
                    >
                </div>
                <div
                    class="flex justify-end gap-2 border-t border-slate-200 pt-3"
                >
                    <button
                        type="button"
                        @click="isTahunModalOpen = false"
                        class="rounded-lg border px-4 py-2"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="tahunForm.processing"
                        class="rounded-lg bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
