<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Plus,
    Edit3,
    Trash2,
    Calendar,
    Layers,
    FolderTree,
    BookOpen,
    Activity,
    Check,
} from '@lucide/vue';
import BaseCard from '@/components/base/BaseCard.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseInput from '@/components/base/BaseInput.vue';
import BaseSelect from '@/components/base/BaseSelect.vue';
import BaseBadge from '@/components/base/BaseBadge.vue';
import BaseTable from '@/components/base/BaseTable.vue';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseConfirmModal from '@/components/base/BaseConfirmModal.vue';
import EmptyState from '@/components/base/EmptyState.vue';
import { formatJurusanName } from '@/lib/utils';
import { useConfirmModal } from '@/composables/useConfirmModal';

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
    konsentrasi_keahlian?: KonsentrasiKeahlian[];
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
    rekap_semester_count?: number;
    nilai_akademik_count?: number;
}

const props = defineProps<{
    bidangList: BidangStudi[];
    programList: ProgramKeahlian[];
    jurusanList: KonsentrasiKeahlian[];
    mapelList: MataPelajaran[];
    ekskulList: Ekstrakurikuler[];
    tahunList: TahunPelajaran[];
}>();

const activeTab = ref<'jurusan' | 'mapel' | 'ekskul' | 'tahun'>('jurusan');
const subKejuruanTab = ref<'konsentrasi' | 'program'>('konsentrasi');

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
    jurusanForm.program_keahlian_id = '';
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

const {
    isOpen: isConfirmOpen,
    isLoading: isConfirmLoading,
    options: confirmOptions,
    openConfirm,
    closeConfirm,
    handleConfirm,
} = useConfirmModal();

const deleteJurusan = (j: KonsentrasiKeahlian) => {
    openConfirm({
        title: 'Hapus Konsentrasi Keahlian',
        message: 'Data kejuruan ini akan dihapus dari data master rujukan sistem.',
        itemInfo: formatJurusanName(j.nama_konsentrasi, j.kode_singkat),
        subInfo: `Program Keahlian: ${j.program_keahlian?.nama_program_keahlian || '-'}`,
        confirmText: 'Ya, Hapus Jurusan',
        variant: 'danger',
        onConfirm: () => {
            router.delete(`/master-data/jurusan/${j.id}`);
        },
    });
};

// --- Modal Program Keahlian Induk ---
const isProgramModalOpen = ref(false);
const editingProgramId = ref<number | null>(null);
const programForm = useForm({
    bidang_studi_id: '',
    kode: '',
    nama_program_keahlian: '',
});

const openCreateProgram = () => {
    editingProgramId.value = null;
    programForm.reset();
    programForm.clearErrors();
    programForm.bidang_studi_id = props.bidangList.length > 0 ? String(props.bidangList[0].id) : '';
    isProgramModalOpen.value = true;
};

const openEditProgram = (p: ProgramKeahlian) => {
    editingProgramId.value = p.id;
    programForm.reset();
    programForm.clearErrors();
    programForm.bidang_studi_id = String(p.bidang_studi_id);
    programForm.kode = p.kode;
    programForm.nama_program_keahlian = p.nama_program_keahlian;
    isProgramModalOpen.value = true;
};

const submitProgram = () => {
    if (editingProgramId.value) {
        programForm.put(`/master-data/program/${editingProgramId.value}`, {
            onSuccess: () => {
                isProgramModalOpen.value = false;
            },
        });
    } else {
        programForm.post('/master-data/program', {
            onSuccess: () => {
                isProgramModalOpen.value = false;
            },
        });
    }
};

const deleteProgram = (p: ProgramKeahlian) => {
    openConfirm({
        title: 'Hapus Program Keahlian',
        message: 'Program keahlian induk ini akan dihapus dari data master.',
        itemInfo: `${p.nama_program_keahlian} (${p.kode})`,
        subInfo: `Bidang Studi: ${p.bidang_studi?.nama_bidang_studi || '-'}`,
        confirmText: 'Ya, Hapus Program',
        variant: 'danger',
        onConfirm: () => {
            router.delete(`/master-data/program/${p.id}`);
        },
    });
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
    openConfirm({
        title: 'Hapus Mata Pelajaran',
        message: 'Mata pelajaran ini akan dihapus dari struktur kurikulum sekolah.',
        itemInfo: `${m.nama_mapel} (${m.kode_mapel})`,
        subInfo: `Kelompok: ${m.kelompok === 'A_UMUM' ? 'Muatan Umum (A)' : 'Kejuruan (B)'} • No. Urut: ${m.no_urut}`,
        confirmText: 'Ya, Hapus Mapel',
        variant: 'danger',
        onConfirm: () => {
            router.delete(`/master-data/mapel/${m.id}`);
        },
    });
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
    openConfirm({
        title: 'Hapus Ekstrakurikuler',
        message: 'Kegiatan ekstrakurikuler ini akan dihapus dari data master sekolah.',
        itemInfo: e.nama_ekskul,
        subInfo: `Kode: ${e.kode_ekskul} • No. Urut: ${e.no_urut}`,
        confirmText: 'Ya, Hapus Ekskul',
        variant: 'danger',
        onConfirm: () => {
            router.delete(`/master-data/ekskul/${e.id}`);
        },
    });
};

// --- Modal Tahun Pelajaran ---
const isTahunModalOpen = ref(false);
const editingTahunId = ref<number | null>(null);
const tahunForm = useForm({
    tahun_pelajaran: '',
});

const openCreateTahun = () => {
    editingTahunId.value = null;
    tahunForm.reset();
    tahunForm.clearErrors();
    isTahunModalOpen.value = true;
};

const openEditTahun = (t: TahunPelajaran) => {
    editingTahunId.value = t.id;
    tahunForm.reset();
    tahunForm.clearErrors();
    tahunForm.tahun_pelajaran = t.tahun_pelajaran;
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
    openConfirm({
        title: 'Hapus Tahun Pelajaran',
        message: 'Tahun pelajaran akademik beserta data nilai di dalamnya akan dihapus dari sistem.',
        itemInfo: `Tahun Pelajaran: ${t.tahun_pelajaran}`,
        subInfo: `Data Tersimpan: ${t.rekap_semester_count || 0} Rekap Semester • ${t.nilai_akademik_count || 0} Nilai Mapel`,
        confirmText: 'Ya, Hapus Tahun',
        variant: 'danger',
        onConfirm: () => {
            router.delete(`/master-data/tahun/${t.id}`);
        },
    });
};

const jurusanColumns = [
    { key: 'no', label: 'No', width: '48px', align: 'center' as const },
    { key: 'kode', label: 'Kode Singkat', width: '130px' },
    { key: 'nama', label: 'Nama Konsentrasi Keahlian' },
    { key: 'program', label: 'Program & Bidang Keahlian' },
    { key: 'aksi', label: 'Aksi', align: 'right' as const, width: '100px' },
];

const programColumns = [
    { key: 'no', label: 'No', width: '48px', align: 'center' as const },
    { key: 'kode', label: 'Kode Program', width: '130px' },
    { key: 'nama', label: 'Nama Program Keahlian Induk' },
    { key: 'bidang', label: 'Bidang Keahlian' },
    { key: 'jumlah_jurusan', label: 'Konsentrasi Naungan', width: '160px', align: 'center' as const },
    { key: 'aksi', label: 'Aksi', align: 'right' as const, width: '100px' },
];

const mapelColumns = [
    { key: 'no', label: 'No Urut', width: '70px', align: 'center' as const },
    { key: 'kode', label: 'Kode Mapel', width: '130px' },
    { key: 'nama', label: 'Nama Mata Pelajaran' },
    { key: 'kelompok', label: 'Kelompok', width: '140px' },
    { key: 'sub', label: 'Sub-Kelompok' },
    { key: 'aksi', label: 'Aksi', align: 'right' as const, width: '100px' },
];

const ekskulColumns = [
    { key: 'no', label: 'No Urut', width: '70px', align: 'center' as const },
    { key: 'kode', label: 'Kode Ekskul', width: '140px' },
    { key: 'nama', label: 'Nama Ekstrakurikuler' },
    { key: 'aksi', label: 'Aksi', align: 'right' as const, width: '100px' },
];

const tahunColumns = [
    { key: 'no', label: 'No', width: '48px', align: 'center' as const },
    { key: 'tahun', label: 'Tahun Pelajaran' },
    { key: 'data_nilai', label: 'Data Nilai Tersimpan', align: 'center' as const, width: '240px' },
    { key: 'aksi', label: 'Aksi', align: 'right' as const, width: '120px' },
];
</script>

<template>
    <Head title="Manajemen Data Master - SI KARANJI" />

    <div class="space-y-6 max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white p-5 rounded-[10px] border border-[#E2E8F0] shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-[#0F3A5F] tracking-tight">
                    Manajemen Data Master
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Kelola data referensi: Konsentrasi Keahlian, Program Keahlian Induk, Mata Pelajaran, Ekstrakurikuler, dan Tahun Pelajaran
                </p>
            </div>
        </div>

        <!-- Tab Navigasi -->
        <div class="bg-white dark:bg-[#131F37] p-1 rounded-[10px] border border-[#E2E8F0] dark:border-[#223354] shadow-xs flex flex-wrap sm:flex-nowrap gap-1">
            <button
                type="button"
                :class="[
                    'flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-[8px] text-xs font-semibold transition-colors cursor-pointer',
                    activeTab === 'jurusan'
                        ? 'bg-[#EAF4FC] dark:bg-[#1E3A5F]/60 text-[#2B7CC2] dark:text-[#38BDF8] border border-[#BFE3FA] dark:border-[#2B7CC2]/40'
                        : 'text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#F1F5F9] hover:bg-[#F8FAFC] dark:hover:bg-[#1E293B]/70',
                ]"
                @click="activeTab = 'jurusan'"
            >
                <Layers class="w-4 h-4" />
                <span>Jurusan & Program Keahlian</span>
            </button>

            <button
                type="button"
                :class="[
                    'flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-[8px] text-xs font-semibold transition-colors cursor-pointer',
                    activeTab === 'mapel'
                        ? 'bg-[#EAF4FC] dark:bg-[#1E3A5F]/60 text-[#2B7CC2] dark:text-[#38BDF8] border border-[#BFE3FA] dark:border-[#2B7CC2]/40'
                        : 'text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#F1F5F9] hover:bg-[#F8FAFC] dark:hover:bg-[#1E293B]/70',
                ]"
                @click="activeTab = 'mapel'"
            >
                <BookOpen class="w-4 h-4" />
                <span>Mata Pelajaran</span>
            </button>

            <button
                type="button"
                :class="[
                    'flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-[8px] text-xs font-semibold transition-colors cursor-pointer',
                    activeTab === 'ekskul'
                        ? 'bg-[#EAF4FC] dark:bg-[#1E3A5F]/60 text-[#2B7CC2] dark:text-[#38BDF8] border border-[#BFE3FA] dark:border-[#2B7CC2]/40'
                        : 'text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#F1F5F9] hover:bg-[#F8FAFC] dark:hover:bg-[#1E293B]/70',
                ]"
                @click="activeTab = 'ekskul'"
            >
                <Activity class="w-4 h-4" />
                <span>Ekstrakurikuler</span>
            </button>

            <button
                type="button"
                :class="[
                    'flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-[8px] text-xs font-semibold transition-colors cursor-pointer',
                    activeTab === 'tahun'
                        ? 'bg-[#EAF4FC] dark:bg-[#1E3A5F]/60 text-[#2B7CC2] dark:text-[#38BDF8] border border-[#BFE3FA] dark:border-[#2B7CC2]/40'
                        : 'text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#F1F5F9] hover:bg-[#F8FAFC] dark:hover:bg-[#1E293B]/70',
                ]"
                @click="activeTab = 'tahun'"
            >
                <Calendar class="w-4 h-4" />
                <span>Tahun Pelajaran</span>
            </button>
        </div>

        <!-- ================= TAB 1: JURUSAN & PROGRAM KEAHLIAN ================= -->
        <div v-if="activeTab === 'jurusan'" class="space-y-4">
            <!-- Sub Switcher Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-[#131F37] p-3 rounded-[10px] border border-[#E2E8F0] dark:border-[#223354] shadow-xs">
                <div class="inline-flex p-1 bg-[#F1F5F9] dark:bg-[#0B1120] rounded-[8px] border border-[#E2E8F0] dark:border-[#223354] self-start sm:self-auto">
                    <button
                        type="button"
                        :class="[
                            'flex items-center gap-1.5 px-3 py-1.5 rounded-[6px] text-xs font-semibold transition-all cursor-pointer',
                            subKejuruanTab === 'konsentrasi'
                                ? 'bg-white dark:bg-[#1E293B] text-[#2B7CC2] dark:text-[#38BDF8] shadow-xs'
                                : 'text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#F1F5F9]'
                        ]"
                        @click="subKejuruanTab = 'konsentrasi'"
                    >
                        <Layers class="w-3.5 h-3.5" />
                        <span>Konsentrasi Keahlian ({{ jurusanList.length }})</span>
                    </button>
                    <button
                        type="button"
                        :class="[
                            'flex items-center gap-1.5 px-3 py-1.5 rounded-[6px] text-xs font-semibold transition-all cursor-pointer',
                            subKejuruanTab === 'program'
                                ? 'bg-white dark:bg-[#1E293B] text-[#2B7CC2] dark:text-[#38BDF8] shadow-xs'
                                : 'text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#F1F5F9]'
                        ]"
                        @click="subKejuruanTab = 'program'"
                    >
                        <FolderTree class="w-3.5 h-3.5" />
                        <span>Program Keahlian Induk ({{ programList.length }})</span>
                    </button>
                </div>

                <!-- Tombol Tambah Sesuai Sub-Tab Aktif -->
                <div>
                    <BaseButton
                        v-if="subKejuruanTab === 'konsentrasi'"
                        variant="primary"
                        size="sm"
                        @click="openCreateJurusan"
                    >
                        <Plus class="w-3.5 h-3.5 mr-1" />
                        <span>Tambah Konsentrasi</span>
                    </BaseButton>
                    <BaseButton
                        v-else
                        variant="primary"
                        size="sm"
                        @click="openCreateProgram"
                    >
                        <Plus class="w-3.5 h-3.5 mr-1" />
                        <span>Tambah Program Induk</span>
                    </BaseButton>
                </div>
            </div>

            <!-- Konten Sub 1: Tabel Konsentrasi Keahlian -->
            <BaseCard
                v-if="subKejuruanTab === 'konsentrasi'"
                title="Daftar Konsentrasi Keahlian (Jurusan)"
                subtitle="Kunci pencocokan kolom kode_jurusan pada berkas import dan lembar Buku Induk"
            >
                <BaseTable :columns="jurusanColumns" :is-empty="jurusanList.length === 0">
                    <tr
                        v-for="(j, idx) in jurusanList"
                        :key="j.id"
                        class="hover:bg-[#F8FAFC] dark:hover:bg-[#1A2846] transition-colors"
                    >
                        <td class="px-4 py-2.5 text-center text-xs text-[#64748B] dark:text-[#94A3B8]">{{ idx + 1 }}</td>
                        <td class="px-4 py-2.5 font-mono font-bold text-[#2B7CC2] dark:text-[#38BDF8] text-xs">{{ j.kode_singkat }}</td>
                        <td class="px-4 py-2.5 font-semibold text-[#0F3A5F] dark:text-[#F1F5F9] text-xs">{{ j.nama_konsentrasi }}</td>
                        <td class="px-4 py-2.5 text-[#64748B] dark:text-[#94A3B8] text-xs">
                            {{ j.program_keahlian?.nama_program_keahlian }}
                            <span class="text-slate-400 dark:text-slate-500 block text-[10px]">
                                Bidang: {{ j.program_keahlian?.bidang_studi?.nama_bidang_studi }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <button
                                type="button"
                                class="p-1 rounded text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#38BDF8] hover:bg-slate-100 dark:hover:bg-[#223354] transition-colors mr-1 cursor-pointer"
                                title="Edit Jurusan"
                                aria-label="Edit Jurusan"
                                @click="openEditJurusan(j)"
                            >
                                <Edit3 class="w-3.5 h-3.5" />
                            </button>
                            <button
                                type="button"
                                class="p-1 rounded text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                                title="Hapus Jurusan"
                                aria-label="Hapus Jurusan"
                                @click="deleteJurusan(j)"
                            >
                                <Trash2 class="w-3.5 h-3.5" />
                            </button>
                        </td>
                    </tr>

                    <template #empty>
                        <EmptyState title="Belum ada data jurusan" description="Tambahkan konsentrasi keahlian untuk memulai." />
                    </template>
                </BaseTable>
            </BaseCard>

            <!-- Konten Sub 2: Tabel Program Keahlian Induk -->
            <BaseCard
                v-else
                title="Daftar Program Keahlian Induk"
                subtitle="Rumpun program kejuruan induk yang menaungi konsentrasi keahlian"
            >
                <BaseTable :columns="programColumns" :is-empty="programList.length === 0">
                    <tr
                        v-for="(p, idx) in programList"
                        :key="p.id"
                        class="hover:bg-[#F8FAFC] dark:hover:bg-[#1A2846] transition-colors"
                    >
                        <td class="px-4 py-2.5 text-center text-xs text-[#64748B] dark:text-[#94A3B8]">{{ idx + 1 }}</td>
                        <td class="px-4 py-2.5 font-mono font-bold text-[#2B7CC2] dark:text-[#38BDF8] text-xs">{{ p.kode }}</td>
                        <td class="px-4 py-2.5 font-semibold text-[#0F3A5F] dark:text-[#F1F5F9] text-xs">{{ p.nama_program_keahlian }}</td>
                        <td class="px-4 py-2.5 text-[#64748B] dark:text-[#94A3B8] text-xs">
                            {{ p.bidang_studi?.nama_bidang_studi || '-' }}
                        </td>
                        <td class="px-4 py-2.5 text-center text-xs">
                            <BaseBadge variant="neutral" size="sm">
                                {{ p.konsentrasi_keahlian?.length || 0 }} Jurusan
                            </BaseBadge>
                        </td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <button
                                type="button"
                                class="p-1 rounded text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#38BDF8] hover:bg-slate-100 dark:hover:bg-[#223354] transition-colors mr-1 cursor-pointer"
                                title="Edit Program"
                                aria-label="Edit Program"
                                @click="openEditProgram(p)"
                            >
                                <Edit3 class="w-3.5 h-3.5" />
                            </button>
                            <button
                                type="button"
                                class="p-1 rounded text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                                title="Hapus Program"
                                aria-label="Hapus Program"
                                @click="deleteProgram(p)"
                            >
                                <Trash2 class="w-3.5 h-3.5" />
                            </button>
                        </td>
                    </tr>

                    <template #empty>
                        <EmptyState title="Belum ada program keahlian" description="Tambahkan program keahlian induk untuk menaungi konsentrasi keahlian." />
                    </template>
                </BaseTable>
            </BaseCard>
        </div>

        <!-- ================= TAB 2: MATA PELAJARAN ================= -->
        <div v-if="activeTab === 'mapel'" class="space-y-4">
            <BaseCard title="Daftar Mata Pelajaran" subtitle="Mata pelajaran yang ditampilkan pada lembar Buku Induk">
                <template #header-actions>
                    <BaseButton variant="primary" size="sm" @click="openCreateMapel">
                        <Plus class="w-3.5 h-3.5" />
                        <span>Tambah Mapel</span>
                    </BaseButton>
                </template>

                <BaseTable :columns="mapelColumns" :is-empty="mapelList.length === 0">
                    <tr
                        v-for="m in mapelList"
                        :key="m.id"
                        class="hover:bg-[#F8FAFC] dark:hover:bg-[#1A2846] transition-colors"
                    >
                        <td class="px-4 py-2.5 text-center text-xs text-[#64748B] dark:text-[#94A3B8]">{{ m.no_urut }}</td>
                        <td class="px-4 py-2.5 font-mono font-bold text-[#2B7CC2] dark:text-[#38BDF8] text-xs">{{ m.kode_mapel }}</td>
                        <td class="px-4 py-2.5 font-semibold text-[#0F3A5F] dark:text-[#F1F5F9] text-xs">{{ m.nama_mapel }}</td>
                        <td class="px-4 py-2.5">
                            <BaseBadge :variant="m.kelompok === 'A_UMUM' ? 'primary' : 'neutral'" size="sm">
                                {{ m.kelompok === 'A_UMUM' ? 'A. UMUM' : 'B. KEJURUAN' }}
                            </BaseBadge>
                        </td>
                        <td class="px-4 py-2.5 text-xs text-[#64748B] dark:text-[#94A3B8]">{{ m.sub_kelompok || '-' }}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <button
                                type="button"
                                class="p-1 rounded text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#38BDF8] hover:bg-slate-100 dark:hover:bg-[#223354] transition-colors mr-1 cursor-pointer"
                                title="Edit Mapel"
                                aria-label="Edit Mapel"
                                @click="openEditMapel(m)"
                            >
                                <Edit3 class="w-3.5 h-3.5" />
                            </button>
                            <button
                                type="button"
                                class="p-1 rounded text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                                title="Hapus Mapel"
                                aria-label="Hapus Mapel"
                                @click="deleteMapel(m)"
                            >
                                <Trash2 class="w-3.5 h-3.5" />
                            </button>
                        </td>
                    </tr>

                    <template #empty>
                        <EmptyState title="Belum ada mata pelajaran" description="Tambahkan mata pelajaran untuk nilai akademik siswa." />
                    </template>
                </BaseTable>
            </BaseCard>
        </div>

        <!-- ================= TAB 3: EKSTRAKURIKULER ================= -->
        <div v-if="activeTab === 'ekskul'" class="space-y-4">
            <BaseCard title="Daftar Ekstrakurikuler" subtitle="Kegiatan pengembangan diri dan ekstrakurikuler siswa">
                <template #header-actions>
                    <BaseButton variant="primary" size="sm" @click="openCreateEkskul">
                        <Plus class="w-3.5 h-3.5" />
                        <span>Tambah Ekskul</span>
                    </BaseButton>
                </template>

                <BaseTable :columns="ekskulColumns" :is-empty="ekskulList.length === 0">
                    <tr
                        v-for="e in ekskulList"
                        :key="e.id"
                        class="hover:bg-[#F8FAFC] dark:hover:bg-[#1A2846] transition-colors"
                    >
                        <td class="px-4 py-2.5 text-center text-xs text-[#64748B] dark:text-[#94A3B8]">{{ e.no_urut }}</td>
                        <td class="px-4 py-2.5 font-mono font-bold text-[#2B7CC2] dark:text-[#38BDF8] text-xs">{{ e.kode_ekskul }}</td>
                        <td class="px-4 py-2.5 font-semibold text-[#0F3A5F] dark:text-[#F1F5F9] text-xs">{{ e.nama_ekskul }}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <button
                                type="button"
                                class="p-1 rounded text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#38BDF8] hover:bg-slate-100 dark:hover:bg-[#223354] transition-colors mr-1 cursor-pointer"
                                title="Edit Ekskul"
                                aria-label="Edit Ekskul"
                                @click="openEditEkskul(e)"
                            >
                                <Edit3 class="w-3.5 h-3.5" />
                            </button>
                            <button
                                type="button"
                                class="p-1 rounded text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                                title="Hapus Ekskul"
                                aria-label="Hapus Ekskul"
                                @click="deleteEkskul(e)"
                            >
                                <Trash2 class="w-3.5 h-3.5" />
                            </button>
                        </td>
                    </tr>

                    <template #empty>
                        <EmptyState title="Belum ada ekstrakurikuler" description="Tambahkan data ekstrakurikuler sekolah." />
                    </template>
                </BaseTable>
            </BaseCard>
        </div>

        <!-- ================= TAB 4: TAHUN PELAJARAN ================= -->
        <div v-if="activeTab === 'tahun'" class="space-y-4">
            <BaseCard title="Daftar Tahun Pelajaran" subtitle="Periode kalender akademik buku induk">
                <template #header-actions>
                    <BaseButton variant="primary" size="sm" @click="openCreateTahun">
                        <Plus class="w-3.5 h-3.5" />
                        <span>Tambah Tahun</span>
                    </BaseButton>
                </template>

                <BaseTable :columns="tahunColumns" :is-empty="tahunList.length === 0">
                    <tr
                        v-for="(t, idx) in tahunList"
                        :key="t.id"
                        class="hover:bg-[#F8FAFC] dark:hover:bg-[#1A2846] transition-colors"
                    >
                        <td class="px-4 py-2.5 text-center text-xs text-[#64748B] dark:text-[#94A3B8]">{{ idx + 1 }}</td>
                        <td class="px-4 py-2.5 font-bold text-[#0F3A5F] dark:text-[#F1F5F9] text-xs">
                            {{ t.tahun_pelajaran }}
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <BaseBadge v-if="(t.rekap_semester_count || 0) > 0 || (t.nilai_akademik_count || 0) > 0" variant="primary" size="sm">
                                {{ t.rekap_semester_count || 0 }} Rekap • {{ t.nilai_akademik_count || 0 }} Nilai Mapel
                            </BaseBadge>
                            <BaseBadge v-else variant="neutral" size="sm">
                                Belum Ada Nilai
                            </BaseBadge>
                        </td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    type="button"
                                    class="p-1 rounded text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#38BDF8] hover:bg-slate-100 dark:hover:bg-[#223354] transition-colors cursor-pointer"
                                    title="Edit"
                                    aria-label="Edit"
                                    @click="openEditTahun(t)"
                                >
                                    <Edit3 class="w-3.5 h-3.5" />
                                </button>
                                <button
                                    type="button"
                                    class="p-1 rounded text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                                    title="Hapus"
                                    aria-label="Hapus"
                                    @click="deleteTahun(t)"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </td>
                    </tr>

                    <template #empty>
                        <EmptyState title="Belum ada tahun pelajaran" description="Tambahkan tahun pelajaran akademik." />
                    </template>
                </BaseTable>
            </BaseCard>
        </div>

        <!-- Modal Jurusan -->
        <BaseModal
            :open="isJurusanModalOpen"
            :title="editingJurusanId ? 'Edit Konsentrasi Keahlian' : 'Tambah Konsentrasi Keahlian'"
            @close="isJurusanModalOpen = false"
        >
            <form @submit.prevent="submitJurusan" class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-[#0F3A5F]">
                            Program Keahlian Induk <span class="text-rose-500">*</span>
                        </label>
                        <button
                            type="button"
                            class="text-[11px] text-[#2B7CC2] hover:underline font-semibold cursor-pointer"
                            @click="isJurusanModalOpen = false; subKejuruanTab = 'program'; openCreateProgram()"
                        >
                            + Tambah Program Baru
                        </button>
                    </div>
                    <BaseSelect
                        v-model="jurusanForm.program_keahlian_id"
                        placeholder="-- Pilih Program Keahlian Induk --"
                        required
                        :error="jurusanForm.errors.program_keahlian_id"
                        hint="Konsentrasi keahlian bernaung di bawah program keahlian induk (misal: TJKT untuk TKJ, PPLG untuk RPL)"
                    >
                        <option v-for="p in programList" :key="p.id" :value="p.id">
                            {{ p.nama_program_keahlian }} ({{ p.kode }})
                        </option>
                    </BaseSelect>
                </div>

                <BaseInput
                    v-model="jurusanForm.kode_singkat"
                    label="Kode Singkat (Contoh: TKJ, RPL, DKV)"
                    placeholder="Contoh: TKJ"
                    required
                    :error="jurusanForm.errors.kode_singkat"
                />

                <BaseInput
                    v-model="jurusanForm.nama_konsentrasi"
                    label="Nama Lengkap Konsentrasi Keahlian"
                    placeholder="Contoh: TEKNIK KOMPUTER DAN JARINGAN"
                    required
                    :error="jurusanForm.errors.nama_konsentrasi"
                />
            </form>

            <template #footer>
                <BaseButton variant="secondary" @click="isJurusanModalOpen = false">
                    Batal
                </BaseButton>
                <BaseButton variant="primary" :loading="jurusanForm.processing" @click="submitJurusan">
                    Simpan
                </BaseButton>
            </template>
        </BaseModal>

        <!-- Modal Program Keahlian Induk -->
        <BaseModal
            :open="isProgramModalOpen"
            :title="editingProgramId ? 'Edit Program Keahlian Induk' : 'Tambah Program Keahlian Induk'"
            @close="isProgramModalOpen = false"
        >
            <form @submit.prevent="submitProgram" class="space-y-4">
                <BaseSelect
                    v-model="programForm.bidang_studi_id"
                    label="Bidang Keahlian / Studi"
                    placeholder="-- Pilih Bidang Keahlian --"
                    required
                    :error="programForm.errors.bidang_studi_id"
                >
                    <option v-for="b in bidangList" :key="b.id" :value="b.id">
                        {{ b.nama_bidang_studi }} ({{ b.kode }})
                    </option>
                </BaseSelect>

                <BaseInput
                    v-model="programForm.kode"
                    label="Kode Singkat Program Keahlian"
                    placeholder="Contoh: TJKT, PPLG, DKV, TO, AKL"
                    required
                    class="uppercase"
                    :error="programForm.errors.kode"
                />

                <BaseInput
                    v-model="programForm.nama_program_keahlian"
                    label="Nama Lengkap Program Keahlian"
                    placeholder="Contoh: TEKNIK JARINGAN KOMPUTER DAN TELEKOMUNIKASI"
                    required
                    class="uppercase"
                    :error="programForm.errors.nama_program_keahlian"
                />
            </form>

            <template #footer>
                <BaseButton variant="secondary" @click="isProgramModalOpen = false">
                    Batal
                </BaseButton>
                <BaseButton variant="primary" :loading="programForm.processing" @click="submitProgram">
                    Simpan
                </BaseButton>
            </template>
        </BaseModal>

        <!-- Modal Mapel -->
        <BaseModal
            :open="isMapelModalOpen"
            :title="editingMapelId ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran'"
            @close="isMapelModalOpen = false"
        >
            <form @submit.prevent="submitMapel" class="space-y-4">
                <BaseInput
                    v-model="mapelForm.kode_mapel"
                    label="Kode Mapel"
                    placeholder="Contoh: MP-01"
                    required
                    :error="mapelForm.errors.kode_mapel"
                />

                <BaseInput
                    v-model="mapelForm.nama_mapel"
                    label="Nama Mata Pelajaran"
                    placeholder="Contoh: Pendidikan Pancasila"
                    required
                    :error="mapelForm.errors.nama_mapel"
                />

                <BaseSelect
                    v-model="mapelForm.kelompok"
                    label="Kelompok Mapel"
                    required
                >
                    <option value="A_UMUM">A. Muatan Umum</option>
                    <option value="B_KEJURUAN">B. Muatan Kejuruan</option>
                </BaseSelect>

                <BaseInput
                    v-model="mapelForm.sub_kelompok"
                    label="Sub Kelompok (Opsional)"
                    placeholder="Contoh: Dasar Program Keahlian"
                />

                <BaseInput
                    v-model.number="mapelForm.no_urut"
                    type="number"
                    label="No Urut Urutan Tampil"
                    required
                />
            </form>

            <template #footer>
                <BaseButton variant="secondary" @click="isMapelModalOpen = false">
                    Batal
                </BaseButton>
                <BaseButton variant="primary" :loading="mapelForm.processing" @click="submitMapel">
                    Simpan
                </BaseButton>
            </template>
        </BaseModal>

        <!-- Modal Ekskul -->
        <BaseModal
            :open="isEkskulModalOpen"
            :title="editingEkskulId ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler'"
            @close="isEkskulModalOpen = false"
        >
            <form @submit.prevent="submitEkskul" class="space-y-4">
                <BaseInput
                    v-model="ekskulForm.kode_ekskul"
                    label="Kode Ekskul"
                    placeholder="Contoh: PRAMUKA"
                    required
                    :error="ekskulForm.errors.kode_ekskul"
                />

                <BaseInput
                    v-model="ekskulForm.nama_ekskul"
                    label="Nama Ekstrakurikuler"
                    placeholder="Contoh: Gerakan Pramuka Gugus Depan"
                    required
                    :error="ekskulForm.errors.nama_ekskul"
                />

                <BaseInput
                    v-model.number="ekskulForm.no_urut"
                    type="number"
                    label="No Urut"
                    required
                />
            </form>

            <template #footer>
                <BaseButton variant="secondary" @click="isEkskulModalOpen = false">
                    Batal
                </BaseButton>
                <BaseButton variant="primary" :loading="ekskulForm.processing" @click="submitEkskul">
                    Simpan
                </BaseButton>
            </template>
        </BaseModal>

        <!-- Modal Tahun Pelajaran -->
        <BaseModal
            :open="isTahunModalOpen"
            :title="editingTahunId ? 'Edit Tahun Pelajaran' : 'Tambah Tahun Pelajaran'"
            @close="isTahunModalOpen = false"
        >
            <form @submit.prevent="submitTahun" class="space-y-4">
                <BaseInput
                    v-model="tahunForm.tahun_pelajaran"
                    label="Tahun Pelajaran (Contoh: 2025/2026)"
                    placeholder="Contoh: 2025/2026"
                    required
                    :error="tahunForm.errors.tahun_pelajaran"
                />
            </form>

            <template #footer>
                <BaseButton variant="secondary" @click="isTahunModalOpen = false">
                    Batal
                </BaseButton>
                <BaseButton variant="primary" :loading="tahunForm.processing" @click="submitTahun">
                    Simpan
                </BaseButton>
            </template>
        </BaseModal>

        <!-- Modal Konfirmasi Tindakan Data Master -->
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
