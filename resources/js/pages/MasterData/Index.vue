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
        <div class="space-y-6 max-w-6xl mx-auto">
            <!-- Header Section -->
            <div class="bg-white p-6 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-blue-950 uppercase tracking-tight flex items-center gap-2">
                        <Database class="w-6 h-6 text-amber-500" />
                        Manajemen Data Master Akademik
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Kelola data referensi utama sistem: Jurusan (Konsentrasi Keahlian), Mata Pelajaran, Ekstrakurikuler, dan Tahun Pelajaran.
                    </p>
                </div>
            </div>

            <!-- Tabs Navigasi Master Data -->
            <div class="flex flex-wrap sm:flex-nowrap gap-2 bg-slate-200/60 p-1.5 rounded-xl text-xs font-bold">
                <button
                    type="button"
                    :class="[
                        'flex-1 py-2.5 px-4 rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'jurusan' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-700 hover:bg-white/60',
                    ]"
                    @click="activeTab = 'jurusan'"
                >
                    <Layers class="w-4 h-4" />
                    <span>Konsentrasi Keahlian (Jurusan)</span>
                </button>

                <button
                    type="button"
                    :class="[
                        'flex-1 py-2.5 px-4 rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'mapel' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-700 hover:bg-white/60',
                    ]"
                    @click="activeTab = 'mapel'"
                >
                    <BookOpen class="w-4 h-4" />
                    <span>Mata Pelajaran (Mapel)</span>
                </button>

                <button
                    type="button"
                    :class="[
                        'flex-1 py-2.5 px-4 rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'ekskul' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-700 hover:bg-white/60',
                    ]"
                    @click="activeTab = 'ekskul'"
                >
                    <Activity class="w-4 h-4" />
                    <span>Ekstrakurikuler</span>
                </button>

                <button
                    type="button"
                    :class="[
                        'flex-1 py-2.5 px-4 rounded-lg transition-all flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'tahun' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-700 hover:bg-white/60',
                    ]"
                    @click="activeTab = 'tahun'"
                >
                    <Calendar class="w-4 h-4" />
                    <span>Tahun Pelajaran</span>
                </button>
            </div>

            <!-- ================= TAB 1: KONSENTRASI KEAHLIAN (JURUSAN) ================= -->
            <div v-if="activeTab === 'jurusan'" class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 flex items-center justify-between border-b border-slate-200">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">
                            Daftar Konsentrasi Keahlian (Jurusan)
                        </h2>
                        <p class="text-xs text-slate-500">Kunci pencocokan kolom <strong>kode_jurusan</strong> pada Excel.</p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                        @click="openCreateJurusan"
                    >
                        <Plus class="w-4 h-4" />
                        <span>+ Tambah Jurusan</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-blue-900 text-white uppercase text-[11px] font-bold">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">No</th>
                                <th class="px-4 py-3 w-32">Kode Singkat</th>
                                <th class="px-4 py-3">Nama Konsentrasi Keahlian</th>
                                <th class="px-4 py-3">Program & Bidang Keahlian</th>
                                <th class="px-4 py-3 text-right w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr
                                v-for="(j, idx) in jurusanList"
                                :key="j.id"
                                class="hover:bg-blue-50/50"
                            >
                                <td class="px-4 py-2.5 text-center font-bold text-slate-500">{{ idx + 1 }}</td>
                                <td class="px-4 py-2.5 font-mono font-bold text-blue-900">{{ j.kode_singkat }}</td>
                                <td class="px-4 py-2.5 font-bold text-slate-900">{{ j.nama_konsentrasi }}</td>
                                <td class="px-4 py-2.5 text-slate-600">
                                    {{ j.program_keahlian?.nama_program_keahlian }}
                                    <span class="text-slate-400 block text-[10px]">
                                        Bidang: {{ j.program_keahlian?.bidang_studi?.nama_bidang_studi }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        class="p-1 rounded text-slate-600 hover:text-blue-700 hover:bg-blue-50 mr-1"
                                        title="Edit"
                                        @click="openEditJurusan(j)"
                                    >
                                        <Edit3 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                        title="Hapus"
                                        @click="deleteJurusan(j)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ================= TAB 2: MATA PELAJARAN ================= -->
            <div v-if="activeTab === 'mapel'" class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 flex items-center justify-between border-b border-slate-200">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">
                            Daftar Mata Pelajaran (Umum & Kejuruan)
                        </h2>
                        <p class="text-xs text-slate-500">Mata pelajaran yang ditampilkan pada lembar Buku Induk matriks nilai.</p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                        @click="openCreateMapel"
                    >
                        <Plus class="w-4 h-4" />
                        <span>+ Tambah Mata Pelajaran</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-blue-900 text-white uppercase text-[11px] font-bold">
                            <tr>
                                <th class="px-4 py-3 w-16 text-center">No Urut</th>
                                <th class="px-4 py-3 w-32">Kode Mapel</th>
                                <th class="px-4 py-3">Nama Mata Pelajaran</th>
                                <th class="px-4 py-3 w-40">Kelompok</th>
                                <th class="px-4 py-3">Sub-Kelompok</th>
                                <th class="px-4 py-3 text-right w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr
                                v-for="m in mapelList"
                                :key="m.id"
                                class="hover:bg-blue-50/50"
                            >
                                <td class="px-4 py-2.5 text-center font-bold text-slate-600">{{ m.no_urut }}</td>
                                <td class="px-4 py-2.5 font-mono font-bold text-blue-900">{{ m.kode_mapel }}</td>
                                <td class="px-4 py-2.5 font-bold text-slate-900">{{ m.nama_mapel }}</td>
                                <td class="px-4 py-2.5">
                                    <span
                                        v-if="m.kelompok === 'A_UMUM'"
                                        class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-sky-100 text-sky-800"
                                    >
                                        A. UMUM
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-indigo-100 text-indigo-800"
                                    >
                                        B. KEJURUAN
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-500">{{ m.sub_kelompok || '-' }}</td>
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        class="p-1 rounded text-slate-600 hover:text-blue-700 hover:bg-blue-50 mr-1"
                                        title="Edit"
                                        @click="openEditMapel(m)"
                                    >
                                        <Edit3 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                        title="Hapus"
                                        @click="deleteMapel(m)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ================= TAB 3: EKSTRAKURIKULER ================= -->
            <div v-if="activeTab === 'ekskul'" class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 flex items-center justify-between border-b border-slate-200">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">
                            Daftar Kegiatan Ekstrakurikuler
                        </h2>
                        <p class="text-xs text-slate-500">Kegiatan non-akademik yang tercantum pada lembar Buku Induk.</p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                        @click="openCreateEkskul"
                    >
                        <Plus class="w-4 h-4" />
                        <span>+ Tambah Ekstrakurikuler</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-blue-900 text-white uppercase text-[11px] font-bold">
                            <tr>
                                <th class="px-4 py-3 w-16 text-center">No Urut</th>
                                <th class="px-4 py-3 w-48">Kode Ekskul</th>
                                <th class="px-4 py-3">Nama Ekstrakurikuler</th>
                                <th class="px-4 py-3 text-right w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr
                                v-for="e in ekskulList"
                                :key="e.id"
                                class="hover:bg-blue-50/50"
                            >
                                <td class="px-4 py-2.5 text-center font-bold text-slate-600">{{ e.no_urut }}</td>
                                <td class="px-4 py-2.5 font-mono font-bold text-blue-900">{{ e.kode_ekskul }}</td>
                                <td class="px-4 py-2.5 font-bold text-slate-900">{{ e.nama_ekskul }}</td>
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        class="p-1 rounded text-slate-600 hover:text-blue-700 hover:bg-blue-50 mr-1"
                                        title="Edit"
                                        @click="openEditEkskul(e)"
                                    >
                                        <Edit3 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                        title="Hapus"
                                        @click="deleteEkskul(e)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ================= TAB 4: TAHUN PELAJARAN ================= -->
            <div v-if="activeTab === 'tahun'" class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 flex items-center justify-between border-b border-slate-200">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">
                            Daftar Tahun Pelajaran
                        </h2>
                        <p class="text-xs text-slate-500">Tentukan tahun pelajaran yang sedang aktif berjalan di sekolah.</p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                        @click="openCreateTahun"
                    >
                        <Plus class="w-4 h-4" />
                        <span>+ Tambah Tahun Pelajaran</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-blue-900 text-white uppercase text-[11px] font-bold">
                            <tr>
                                <th class="px-4 py-3 w-16 text-center">No</th>
                                <th class="px-4 py-3">Tahun Pelajaran</th>
                                <th class="px-4 py-3 text-center w-36">Status Aktif</th>
                                <th class="px-4 py-3 text-right w-44">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr
                                v-for="(t, idx) in tahunList"
                                :key="t.id"
                                class="hover:bg-blue-50/50"
                            >
                                <td class="px-4 py-2.5 text-center font-bold text-slate-500">{{ idx + 1 }}</td>
                                <td class="px-4 py-2.5 font-bold text-slate-900 text-sm font-mono">{{ t.tahun_pelajaran }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <span
                                        v-if="t.is_aktif"
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300"
                                    >
                                        <CheckCircle2 class="w-3.5 h-3.5" />
                                        Aktif Berjalan
                                    </span>
                                    <span v-else class="text-slate-400 text-xs">Tidak Aktif</span>
                                </td>
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    <button
                                        v-if="!t.is_aktif"
                                        type="button"
                                        class="px-2.5 py-1 rounded bg-sky-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200 text-[11px] font-bold transition-colors mr-2 cursor-pointer"
                                        title="Jadikan Tahun Aktif"
                                        @click="setTahunAktif(t)"
                                    >
                                        Set Aktif
                                    </button>
                                    <button
                                        type="button"
                                        class="p-1 rounded text-slate-600 hover:text-blue-700 hover:bg-blue-50 mr-1"
                                        title="Edit"
                                        @click="openEditTahun(t)"
                                    >
                                        <Edit3 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                        title="Hapus"
                                        @click="deleteTahun(t)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= MODAL JURUSAN ================= -->
        <div v-if="isJurusanModalOpen" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 bg-blue-900 text-white flex items-center justify-between">
                    <h3 class="font-black text-sm uppercase">
                        {{ editingJurusanId ? 'Edit Konsentrasi Keahlian' : 'Tambah Konsentrasi Keahlian' }}
                    </h3>
                    <button @click="isJurusanModalOpen = false" class="text-blue-200 hover:text-white p-1">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <form @submit.prevent="submitJurusan" class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Program Keahlian *</label>
                        <select v-model="jurusanForm.program_keahlian_id" required class="w-full px-3 py-2 border rounded-lg">
                            <option v-for="p in programList" :key="p.id" :value="p.id">
                                {{ p.nama_program_keahlian }} ({{ p.kode }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kode Singkat (Dipakai di Excel) *</label>
                        <input v-model="jurusanForm.kode_singkat" type="text" required placeholder="Contoh: TKJ" class="w-full px-3 py-2 border rounded-lg font-mono uppercase" />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Konsentrasi Keahlian *</label>
                        <input v-model="jurusanForm.nama_konsentrasi" type="text" required placeholder="Contoh: TEKNIK KOMPUTER & JARINGAN (TKJ)" class="w-full px-3 py-2 border rounded-lg uppercase" />
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                        <button type="button" @click="isJurusanModalOpen = false" class="px-4 py-2 border rounded-lg">Batal</button>
                        <button type="submit" :disabled="jurusanForm.processing" class="px-5 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= MODAL MATA PELAJARAN ================= -->
        <div v-if="isMapelModalOpen" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 bg-blue-900 text-white flex items-center justify-between">
                    <h3 class="font-black text-sm uppercase">
                        {{ editingMapelId ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran' }}
                    </h3>
                    <button @click="isMapelModalOpen = false" class="text-blue-200 hover:text-white p-1">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <form @submit.prevent="submitMapel" class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kode Mapel (Dipakai di Excel) *</label>
                        <input v-model="mapelForm.kode_mapel" type="text" required placeholder="Contoh: MTK / INFORMATIKA" class="w-full px-3 py-2 border rounded-lg font-mono uppercase" />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Mata Pelajaran *</label>
                        <input v-model="mapelForm.nama_mapel" type="text" required placeholder="Contoh: Matematika" class="w-full px-3 py-2 border rounded-lg" />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kelompok Mapel *</label>
                        <select v-model="mapelForm.kelompok" class="w-full px-3 py-2 border rounded-lg font-bold">
                            <option value="A_UMUM">A. Kelompok Mata Pelajaran Umum</option>
                            <option value="B_KEJURUAN">B. Kelompok Mata Pelajaran Kejuruan</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Sub-Kelompok (Opsional)</label>
                            <input v-model="mapelForm.sub_kelompok" type="text" placeholder="Contoh: Pendidikan Agama / Pilihan" class="w-full px-3 py-2 border rounded-lg" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Urut Tampil *</label>
                            <input v-model.number="mapelForm.no_urut" type="number" min="1" class="w-full px-3 py-2 border rounded-lg font-bold" />
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                        <button type="button" @click="isMapelModalOpen = false" class="px-4 py-2 border rounded-lg">Batal</button>
                        <button type="submit" :disabled="mapelForm.processing" class="px-5 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= MODAL EKSTRAKURIKULER ================= -->
        <div v-if="isEkskulModalOpen" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 bg-blue-900 text-white flex items-center justify-between">
                    <h3 class="font-black text-sm uppercase">
                        {{ editingEkskulId ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler' }}
                    </h3>
                    <button @click="isEkskulModalOpen = false" class="text-blue-200 hover:text-white p-1">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <form @submit.prevent="submitEkskul" class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kode Ekskul (Dipakai di Excel) *</label>
                        <input v-model="ekskulForm.kode_ekskul" type="text" required placeholder="Contoh: EKSKUL_PRAMUKA" class="w-full px-3 py-2 border rounded-lg font-mono uppercase" />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Ekstrakurikuler *</label>
                        <input v-model="ekskulForm.nama_ekskul" type="text" required placeholder="Contoh: Pramuka" class="w-full px-3 py-2 border rounded-lg" />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Urut Tampil *</label>
                        <input v-model.number="ekskulForm.no_urut" type="number" min="1" class="w-full px-3 py-2 border rounded-lg font-bold" />
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                        <button type="button" @click="isEkskulModalOpen = false" class="px-4 py-2 border rounded-lg">Batal</button>
                        <button type="submit" :disabled="ekskulForm.processing" class="px-5 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= MODAL TAHUN PELAJARAN ================= -->
        <div v-if="isTahunModalOpen" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 bg-blue-900 text-white flex items-center justify-between">
                    <h3 class="font-black text-sm uppercase">
                        {{ editingTahunId ? 'Edit Tahun Pelajaran' : 'Tambah Tahun Pelajaran' }}
                    </h3>
                    <button @click="isTahunModalOpen = false" class="text-blue-200 hover:text-white p-1">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <form @submit.prevent="submitTahun" class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Pelajaran *</label>
                        <input v-model="tahunForm.tahun_pelajaran" type="text" required placeholder="Contoh: 2025/2026" class="w-full px-3 py-2 border rounded-lg font-mono font-bold" />
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <input v-model="tahunForm.is_aktif" type="checkbox" id="checkAktif" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500" />
                        <label for="checkAktif" class="font-bold text-slate-800">Set sebagai Tahun Pelajaran Aktif</label>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                        <button type="button" @click="isTahunModalOpen = false" class="px-4 py-2 border rounded-lg">Batal</button>
                        <button type="submit" :disabled="tahunForm.processing" class="px-5 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
</template>
