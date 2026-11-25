<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Search,
    Plus,
    FileSpreadsheet,
    BookOpen,
    Edit3,
    Trash2,
    Filter,
    UserCheck,
    Camera,
    RotateCcw,
    User,
    Users,
    GraduationCap,
    UploadCloud,
    FileText,
    Building2,
    CheckCircle2,
    X,
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

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingSiswaId = ref<number | null>(null);
const activeModalTab = ref<'identitas' | 'ortu' | 'riwayat'>('identitas');
const currentPasfotoUrl = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const pasfotoError = ref<string>('');

const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const removeSelectedPhoto = () => {
    form.pasfoto = null;
    pasfotoError.value = '';
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

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
    currentPasfotoUrl.value = null;
    activeModalTab.value = 'identitas';
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
    currentPasfotoUrl.value = item.pasfoto_url || null;
    activeModalTab.value = 'identitas';
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

const ALLOWED_PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
const ALLOWED_PHOTO_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

const handlePhotoChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    const ext = file.name.split('.').pop()?.toLowerCase() || '';
    if (!ALLOWED_PHOTO_EXTENSIONS.includes(ext) || (file.type && !ALLOWED_PHOTO_MIMES.includes(file.type))) {
        pasfotoError.value = `Tipe file keliru (.${ext.toUpperCase()})! Gunakan foto format JPG, JPEG, PNG, atau WEBP.`;
        form.pasfoto = null;
        input.value = '';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        pasfotoError.value = 'Ukuran file foto terlalu besar (Maksimal 2MB).';
        form.pasfoto = null;
        input.value = '';
        return;
    }

    pasfotoError.value = '';
    form.pasfoto = file;
};

const submitForm = () => {
    if (isEditing.value && editingSiswaId.value) {
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

const {
    isOpen: isConfirmOpen,
    isLoading: isConfirmLoading,
    options: confirmOptions,
    openConfirm,
    closeConfirm,
    handleConfirm,
} = useConfirmModal();

const deleteSiswa = (item: SiswaItem) => {
    openConfirm({
        title: 'Hapus Data Siswa',
        message: 'Seluruh berkas profil, rekaman nilai rapor, dan lembar buku induk siswa ini akan dihapus permanen.',
        itemInfo: item.nama_lengkap,
        subInfo: `NIS: ${item.nis} • NISN: ${item.nisn} • ${item.kelas_saat_ini || '-'} (${item.konsentrasi_keahlian?.kode_singkat || '-'})`,
        confirmText: 'Ya, Hapus Siswa',
        variant: 'danger',
        onConfirm: () => {
            router.delete(`/siswa/${item.id}`);
        },
    });
};

const tableColumns = [
    { key: 'foto', label: 'Foto', width: '56px', align: 'center' as const },
    { key: 'nis', label: 'NIS / NISN' },
    { key: 'nama', label: 'Nama Lengkap' },
    { key: 'jk', label: 'L/P', width: '48px', align: 'center' as const },
    { key: 'kelas', label: 'Kelas' },
    { key: 'jurusan', label: 'Konsentrasi Keahlian' },
    { key: 'status', label: 'Status', align: 'center' as const },
    { key: 'aksi', label: 'Aksi', align: 'right' as const },
];
</script>

<template>
    <Head title="Direktori Siswa - SI KARANJI" />

    <div class="space-y-5 max-w-7xl mx-auto">
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-[10px] border border-[#E2E8F0] shadow-xs">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-[#0F3A5F] tracking-tight">
                    Direktori Siswa
                </h1>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Pencarian data siswa, pengelolaan biodata, dan akses lembar Buku Induk
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <BaseButton href="/import" variant="secondary" size="md">
                    <FileSpreadsheet class="w-4 h-4 text-[#2B7CC2]" />
                    <span>Import Excel</span>
                </BaseButton>

                <BaseButton variant="primary" size="md" @click="openCreateModal">
                    <Plus class="w-4 h-4" />
                    <span>Tambah Siswa</span>
                </BaseButton>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <BaseCard padding="sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="relative">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari NIS, NISN, atau Nama Siswa..."
                        class="w-full pl-9 pr-3 py-2 rounded-[8px] border border-[#E2E8F0] text-xs sm:text-sm text-[#0F3A5F] bg-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2] transition-colors"
                    />
                </div>

                <BaseSelect v-model="jurusanId" placeholder="Semua Konsentrasi Keahlian">
                    <option
                        v-for="jurusan in jurusanList"
                        :key="jurusan.id"
                        :value="jurusan.id"
                    >
                        {{ formatJurusanName(jurusan.nama_konsentrasi, jurusan.kode_singkat) }}
                    </option>
                </BaseSelect>

                <BaseSelect v-model="kelas" placeholder="Semua Rombel / Kelas">
                    <option v-for="k in kelasList" :key="k" :value="k">
                        {{ k }}
                    </option>
                </BaseSelect>

                <BaseSelect v-model="status" placeholder="Semua Status Siswa">
                    <option value="AKTIF">AKTIF</option>
                    <option value="TAMAT">TAMAT / LULUS</option>
                    <option value="KELUAR">PINDAH / KELUAR</option>
                </BaseSelect>
            </div>

            <!-- Indicator Filter Aktif -->
            <div
                v-if="search || jurusanId || kelas || status"
                class="flex items-center justify-between pt-3 mt-3 border-t border-[#E2E8F0] text-xs text-[#64748B]"
            >
                <span class="flex items-center gap-1.5 font-medium">
                    <Filter class="w-3.5 h-3.5 text-[#2B7CC2]" />
                    Filter aktif (Ditemukan: <strong class="text-[#0F3A5F]">{{ siswa.total }}</strong> siswa)
                </span>
                <button
                    type="button"
                    class="text-xs font-semibold text-[#2B7CC2] hover:underline flex items-center gap-1 cursor-pointer"
                    @click="resetFilters"
                >
                    <RotateCcw class="w-3 h-3" />
                    <span>Reset Filter</span>
                </button>
            </div>
        </BaseCard>

        <!-- Tabel Siswa -->
        <BaseTable :columns="tableColumns" :is-empty="siswa.data.length === 0">
            <tr
                v-for="item in siswa.data"
                :key="item.id"
                class="hover:bg-[#F8FAFC] dark:hover:bg-[#1A2846] transition-colors"
            >
                <!-- Pasfoto -->
                <td class="px-3 py-2 text-center">
                    <div class="w-8 h-10 bg-slate-100 dark:bg-[#0B1120] border border-[#E2E8F0] dark:border-[#223354] rounded-[4px] overflow-hidden mx-auto flex items-center justify-center text-slate-400">
                        <img
                            v-if="item.pasfoto_url"
                            :src="item.pasfoto_url"
                            :alt="item.nama_lengkap"
                            class="w-full h-full object-cover"
                        />
                        <UserCheck v-else class="w-4 h-4 text-slate-300 dark:text-slate-600" />
                    </div>
                </td>

                <!-- NIS / NISN -->
                <td class="px-4 py-2.5 font-mono text-xs text-[#64748B] dark:text-[#94A3B8] whitespace-nowrap">
                    <div class="font-bold text-[#0F3A5F] dark:text-[#F1F5F9]">{{ item.nis }}</div>
                    <div class="text-[11px]">{{ item.nisn }}</div>
                </td>

                <!-- Nama Lengkap -->
                <td class="px-4 py-2.5 whitespace-nowrap">
                    <Link
                        :href="`/siswa/${item.id}/buku-induk`"
                        class="font-semibold text-[#0F3A5F] dark:text-[#F1F5F9] hover:text-[#2B7CC2] dark:hover:text-[#38BDF8] transition-colors block"
                    >
                        {{ item.nama_lengkap }}
                    </Link>
                    <div class="text-[11px] text-[#64748B] dark:text-[#94A3B8]">
                        {{ item.tempat_lahir || '-' }}, {{ item.tanggal_lahir ? item.tanggal_lahir.substring(0, 10) : '-' }}
                    </div>
                </td>

                <!-- Jenis Kelamin -->
                <td class="px-2 py-2.5 text-center whitespace-nowrap font-semibold">
                    <BaseBadge :variant="item.jenis_kelamin === 'L' ? 'primary' : 'neutral'" size="sm">
                        {{ item.jenis_kelamin }}
                    </BaseBadge>
                </td>

                <!-- Kelas -->
                <td class="px-4 py-2.5 whitespace-nowrap text-xs font-medium text-[#0F3A5F] dark:text-[#F1F5F9]">
                    {{ item.kelas_saat_ini || '-' }}
                </td>

                <!-- Konsentrasi Keahlian -->
                <td class="px-4 py-2.5 whitespace-nowrap text-xs">
                    <div v-if="item.konsentrasi_keahlian" class="font-medium text-[#0F3A5F] dark:text-[#F1F5F9]">
                        {{ formatJurusanName(item.konsentrasi_keahlian.nama_konsentrasi, item.konsentrasi_keahlian.kode_singkat) }}
                    </div>
                    <span v-else class="text-slate-400 dark:text-slate-500 italic">Belum ditentukan</span>
                </td>

                <!-- Status Siswa -->
                <td class="px-4 py-2.5 text-center whitespace-nowrap">
                    <BaseBadge
                        v-if="item.status_siswa === 'AKTIF'"
                        variant="success"
                        size="sm"
                        dot
                    >
                        AKTIF
                    </BaseBadge>
                    <BaseBadge
                        v-else-if="item.status_siswa === 'TAMAT'"
                        variant="primary"
                        size="sm"
                        dot
                    >
                        TAMAT
                    </BaseBadge>
                    <BaseBadge
                        v-else
                        variant="danger"
                        size="sm"
                        dot
                    >
                        KELUAR
                    </BaseBadge>
                </td>

                <!-- Tombol Aksi -->
                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1.5">
                        <Link
                            :href="`/siswa/${item.id}/buku-induk`"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] bg-[#EAF4FC] dark:bg-[#1E3A5F]/60 hover:bg-[#BFE3FA]/50 dark:hover:bg-[#1E3A5F] text-[#2B7CC2] dark:text-[#38BDF8] border border-[#BFE3FA] dark:border-[#2B7CC2]/40 text-xs font-semibold transition-colors"
                            title="Buka Buku Induk"
                            aria-label="Buka Buku Induk"
                        >
                            <BookOpen class="w-3.5 h-3.5" />
                            <span>Buku Induk</span>
                        </Link>

                        <button
                            type="button"
                            class="p-1.5 rounded-[6px] text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#38BDF8] hover:bg-slate-100 dark:hover:bg-[#223354] transition-colors cursor-pointer"
                            title="Edit Biodata Siswa"
                            aria-label="Edit Biodata Siswa"
                            @click="openEditModal(item)"
                        >
                            <Edit3 class="w-3.5 h-3.5" />
                        </button>

                        <button
                            type="button"
                            class="p-1.5 rounded-[6px] text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                            title="Hapus Siswa"
                            aria-label="Hapus Siswa"
                            @click="deleteSiswa(item)"
                        >
                            <Trash2 class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </td>
            </tr>

            <template #empty>
                <EmptyState
                    title="Tidak ada data siswa ditemukan"
                    description="Coba ubah kata kunci pencarian atau sesuaikan opsi filter di atas."
                />
            </template>
        </BaseTable>

        <!-- Pagination -->
        <div class="p-4 bg-white dark:bg-[#131F37] rounded-[10px] border border-[#E2E8F0] dark:border-[#223354] shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#64748B] dark:text-[#94A3B8]">
            <div>
                Menampilkan <strong class="text-[#0F3A5F] dark:text-[#F1F5F9]">{{ siswa.from || 0 }}</strong> - <strong class="text-[#0F3A5F] dark:text-[#F1F5F9]">{{ siswa.to || 0 }}</strong> dari <strong class="text-[#0F3A5F] dark:text-[#F1F5F9]">{{ siswa.total }}</strong> siswa
            </div>

            <div class="flex items-center gap-1">
                <template v-for="(link, i) in siswa.links" :key="i">
                    <!-- Halaman Aktif (Tidak perlu klik, tampil angka dengan warna aksen) -->
                    <span
                        v-if="link.active"
                        class="min-w-8 h-8 px-2.5 rounded-[6px] text-xs font-bold bg-[#2B7CC2] dark:bg-[#2563EB] text-white shadow-xs inline-flex items-center justify-center select-none"
                    >
                        <span v-html="link.label" />
                    </span>

                    <!-- Halaman Lain yang Dapat Diklik -->
                    <Link
                        v-else-if="link.url"
                        :href="link.url"
                        class="min-w-8 h-8 px-2.5 rounded-[6px] text-xs font-medium bg-white dark:bg-[#1E293B] border border-[#E2E8F0] dark:border-[#223354] text-[#0F3A5F] dark:text-[#F1F5F9] hover:bg-slate-50 dark:hover:bg-[#283548] transition-colors cursor-pointer inline-flex items-center justify-center select-none"
                    >
                        <span v-html="link.label" />
                    </Link>

                    <!-- Tombol Disabled (Misal Previous / Next di ujung halaman) -->
                    <span
                        v-else
                        class="min-w-8 h-8 px-2.5 rounded-[6px] text-xs font-medium text-slate-400 dark:text-slate-600 select-none inline-flex items-center justify-center opacity-70"
                    >
                        <span v-html="link.label" />
                    </span>
                </template>
            </div>
        </div>

        <!-- Modal Tambah / Edit Siswa -->
        <BaseModal
            :open="isModalOpen"
            :title="isEditing ? 'Edit Data Siswa' : 'Tambah Siswa Baru'"
            :description="isEditing ? 'Perbarui informasi biodata lengkap, data orang tua, dan riwayat siswa' : 'Isi formulir biodata lengkap siswa untuk dicatat pada Buku Induk'"
            size="3xl"
            @close="closeModal"
        >
            <!-- Hidden File Input for Pasfoto -->
            <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handlePhotoChange"
            />

            <!-- Sub-Tab Segmented Switcher (Modern & Rapi) -->
            <div class="flex items-center gap-1.5 p-1 bg-[#F1F5F9] rounded-xl border border-[#E2E8F0] mb-5">
                <button
                    type="button"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 py-2 px-3 text-xs rounded-lg transition-all cursor-pointer font-medium select-none',
                        activeModalTab === 'identitas'
                            ? 'bg-white text-[#2B7CC2] font-bold shadow-xs'
                            : 'text-[#64748B] hover:text-[#0F3A5F] hover:bg-slate-200/50',
                    ]"
                    @click="activeModalTab = 'identitas'"
                >
                    <User class="w-3.5 h-3.5" />
                    <span>1. Identitas Pokok</span>
                </button>
                <button
                    type="button"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 py-2 px-3 text-xs rounded-lg transition-all cursor-pointer font-medium select-none',
                        activeModalTab === 'ortu'
                            ? 'bg-white text-[#2B7CC2] font-bold shadow-xs'
                            : 'text-[#64748B] hover:text-[#0F3A5F] hover:bg-slate-200/50',
                    ]"
                    @click="activeModalTab = 'ortu'"
                >
                    <Users class="w-3.5 h-3.5" />
                    <span>2. Data Orang Tua / Wali</span>
                </button>
                <button
                    type="button"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 py-2 px-3 text-xs rounded-lg transition-all cursor-pointer font-medium select-none',
                        activeModalTab === 'riwayat'
                            ? 'bg-white text-[#2B7CC2] font-bold shadow-xs'
                            : 'text-[#64748B] hover:text-[#0F3A5F] hover:bg-slate-200/50',
                    ]"
                    @click="activeModalTab = 'riwayat'"
                >
                    <GraduationCap class="w-3.5 h-3.5" />
                    <span>3. Riwayat Masuk & Lulus</span>
                </button>
            </div>

            <form id="siswaForm" @submit.prevent="submitForm" class="space-y-4 text-xs">
                <!-- ================= TAB 1: IDENTITAS POKOK SISWA ================= -->
                <div v-show="activeModalTab === 'identitas'" class="space-y-4">
                    <!-- Kartu Atas: Pasfoto & Data Pokok -->
                    <div class="p-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0]">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
                            <!-- Kolom Kiri: Pasfoto 3x4 Frame -->
                            <div class="md:col-span-3 flex flex-col items-center justify-center text-center p-3 bg-white rounded-lg border border-[#E2E8F0] shadow-2xs">
                                <div class="w-24 h-32 bg-[#F8FAFC] border-2 border-dashed border-[#CBD5E1] rounded-lg flex items-center justify-center overflow-hidden mb-2 relative shadow-inner">
                                    <img
                                        v-if="form.pasfoto"
                                        :src="URL.createObjectURL(form.pasfoto)"
                                        alt="Pratinjau Foto"
                                        class="w-full h-full object-cover"
                                    />
                                    <img
                                        v-else-if="currentPasfotoUrl"
                                        :src="currentPasfotoUrl"
                                        alt="Pasfoto Siswa"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="flex flex-col items-center justify-center text-slate-300">
                                        <Camera class="w-7 h-7 mb-1" />
                                        <span class="text-[9px] text-[#64748B] font-medium">Pasfoto 3x4</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 w-full">
                                    <BaseButton
                                        type="button"
                                        variant="secondary"
                                        size="sm"
                                        class="flex-1 text-[11px] py-1"
                                        @click="triggerFileInput"
                                    >
                                        <UploadCloud class="w-3 h-3 mr-1 text-[#2B7CC2]" />
                                        <span>{{ form.pasfoto || currentPasfotoUrl ? 'Ganti Foto' : 'Pilih Foto' }}</span>
                                    </BaseButton>

                                    <button
                                        v-if="form.pasfoto"
                                        type="button"
                                        class="p-1 rounded text-rose-500 hover:bg-rose-50 transition-colors"
                                        title="Batal pilih foto"
                                        @click="removeSelectedPhoto"
                                    >
                                        <X class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <span class="text-[9.5px] text-[#64748B] mt-1.5">Format JPG/PNG/WEBP, maks. 2MB</span>
                                <span v-if="pasfotoError || form.errors.pasfoto" class="text-[10px] font-semibold text-rose-600 mt-1 leading-tight">
                                    {{ pasfotoError || form.errors.pasfoto }}
                                </span>
                            </div>

                            <!-- Kolom Kanan: Form Data Identitas Pokok -->
                            <div class="md:col-span-9 space-y-3">
                                <!-- Baris 1: NIS & NISN -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <BaseInput
                                        v-model="form.nis"
                                        label="NIS (Nomor Induk Siswa)"
                                        placeholder="Contoh: 24251001"
                                        required
                                        :error="form.errors.nis"
                                    />
                                    <BaseInput
                                        v-model="form.nisn"
                                        label="NISN (10 Digit Nasional)"
                                        placeholder="Contoh: 0081234567"
                                        required
                                        :error="form.errors.nisn"
                                    />
                                </div>

                                <!-- Baris 2: Nama Lengkap Siswa -->
                                <BaseInput
                                    v-model="form.nama_lengkap"
                                    label="Nama Lengkap Siswa"
                                    placeholder="Nama lengkap sesuai ijazah SMP"
                                    required
                                    class="uppercase"
                                    :error="form.errors.nama_lengkap"
                                />

                                <!-- Baris 3: Konsentrasi Keahlian (Jurusan) Diberi Lebar Penuh agar Tidak Terpotong -->
                                <BaseSelect
                                    v-model="form.konsentrasi_keahlian_id"
                                    label="Konsentrasi Keahlian (Jurusan)"
                                >
                                    <option value="">-- Pilih Konsentrasi Keahlian / Jurusan --</option>
                                    <option v-for="j in jurusanList" :key="j.id" :value="j.id">
                                        {{ formatJurusanName(j.nama_konsentrasi, j.kode_singkat) }}
                                    </option>
                                </BaseSelect>

                                <!-- Baris 4: Jenis Kelamin, Rombel, & Status Keaktifan -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <BaseSelect
                                        v-model="form.jenis_kelamin"
                                        label="Jenis Kelamin"
                                        required
                                    >
                                        <option value="L">Laki-Laki (L)</option>
                                        <option value="P">Perempuan (P)</option>
                                    </BaseSelect>

                                    <BaseInput
                                        v-model="form.kelas_saat_ini"
                                        label="Rombel / Kelas Saat Ini"
                                        placeholder="Contoh: X TKJ 1"
                                    />

                                    <BaseSelect
                                        v-model="form.status_siswa"
                                        label="Status Siswa"
                                        required
                                    >
                                        <option value="AKTIF">AKTIF</option>
                                        <option value="TAMAT">TAMAT / LULUS</option>
                                        <option value="KELUAR">PINDAH / KELUAR</option>
                                    </BaseSelect>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Bawah: Kelahiran, Agama & Alamat Domisili -->
                    <div class="p-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <BaseInput
                                v-model="form.tempat_lahir"
                                label="Tempat Lahir"
                                placeholder="Kota / Kabupaten Lahir"
                            />
                            <BaseInput
                                v-model="form.tanggal_lahir"
                                type="date"
                                label="Tanggal Lahir"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <BaseSelect
                                v-model="form.agama"
                                label="Agama"
                            >
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </BaseSelect>
                            <BaseInput
                                v-model="form.warga_negara"
                                label="Kewarganegaraan"
                                placeholder="WNI"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[#0F3A5F]">Alamat Tinggal / Domisili Siswa</label>
                            <textarea
                                v-model="form.alamat_siswa"
                                rows="2"
                                placeholder="Alamat jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota..."
                                class="w-full bg-white text-xs text-[#0F3A5F] rounded-[8px] border border-[#E2E8F0] p-2.5 focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2] transition-colors"
                            />
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 2: DATA ORANG TUA / WALI ================= -->
                <div v-show="activeModalTab === 'ortu'" class="space-y-4">
                    <div class="p-5 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] space-y-4">
                        <div class="flex items-start gap-2.5 pb-3 border-b border-[#E2E8F0]">
                            <div class="w-8 h-8 rounded-lg bg-[#EAF4FC] text-[#2B7CC2] flex items-center justify-center shrink-0">
                                <Users class="w-4 h-4" />
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-[#0F3A5F]">Data Orang Tua / Wali Siswa</h4>
                                <p class="text-[11px] text-[#64748B]">
                                    Informasi ini akan dicatat dan dicetak pada lembar resmi Buku Induk bagian Data Orang Tua / Wali.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <BaseInput
                                v-model="form.nama_orang_tua"
                                label="Nama Lengkap Orang Tua / Wali"
                                placeholder="Nama Ayah, Ibu, atau Wali Murid"
                            />
                            <BaseInput
                                v-model="form.pekerjaan_orang_tua"
                                label="Pekerjaan Orang Tua / Wali"
                                placeholder="Contoh: Wiraswasta / Karyawan / PNS"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[#0F3A5F]">Alamat Rumah / Tempat Tinggal Orang Tua</label>
                            <textarea
                                v-model="form.alamat_orang_tua"
                                rows="3"
                                placeholder="Alamat lengkap tempat tinggal orang tua / wali..."
                                class="w-full bg-white text-xs text-[#0F3A5F] rounded-[8px] border border-[#E2E8F0] p-2.5 focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] focus:border-[#2B7CC2] transition-colors"
                            />
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 3: RIWAYAT PENERIMAAN & KELULUSAN ================= -->
                <div v-show="activeModalTab === 'riwayat'" class="space-y-4">
                    <!-- Seksi 1: Riwayat Penerimaan Masuk -->
                    <div class="p-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] space-y-3">
                        <div class="flex items-center gap-2 pb-2 border-b border-[#E2E8F0]">
                            <FileText class="w-4 h-4 text-[#2B7CC2]" />
                            <h4 class="text-xs font-bold text-[#0F3A5F] uppercase tracking-wide">
                                1. Riwayat Penerimaan Siswa Baru
                            </h4>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <BaseInput
                                v-model="form.diterima_tanggal"
                                type="date"
                                label="Diterima Mulai Tanggal"
                            />
                            <BaseInput
                                v-model="form.diterima_asal_sekolah"
                                label="Asal Sekolah (SMP / MTs)"
                                placeholder="Nama SMP/MTs asal"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <BaseInput
                                v-model="form.diterima_nomor_sttb"
                                label="Nomor STTB / Ijazah SMP"
                                placeholder="Contoh: DN-01/D-SMP/..."
                            />
                            <BaseInput
                                v-model="form.diterima_tanggal_sttb"
                                type="date"
                                label="Tanggal Ijazah / STTB SMP"
                            />
                        </div>
                    </div>

                    <!-- Seksi 2: Status Kelulusan / Tamat Belajar -->
                    <div class="p-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] space-y-3">
                        <div class="flex items-center gap-2 pb-2 border-b border-[#E2E8F0]">
                            <GraduationCap class="w-4 h-4 text-[#2B7CC2]" />
                            <h4 class="text-xs font-bold text-[#0F3A5F] uppercase tracking-wide">
                                2. Status Tamat Belajar / Kelulusan SMK
                            </h4>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <BaseInput
                                v-model="form.tamat_nomor_sttb"
                                label="Nomor Ijazah / STTB SMK"
                                placeholder="Contoh: M-SMK/06-3/..."
                            />
                            <BaseInput
                                v-model="form.tamat_tanggal_sttb"
                                type="date"
                                label="Tanggal Kelulusan / STTB SMK"
                            />
                        </div>
                    </div>

                    <!-- Seksi 3: Meninggalkan Sekolah / Mutasi -->
                    <div class="p-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] space-y-3">
                        <div class="flex items-center gap-2 pb-2 border-b border-[#E2E8F0]">
                            <Building2 class="w-4 h-4 text-[#2B7CC2]" />
                            <h4 class="text-xs font-bold text-[#0F3A5F] uppercase tracking-wide">
                                3. Meninggalkan Sekolah / Pindah (Jika Berlaku)
                            </h4>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <BaseInput
                                v-model="form.keluar_tanggal"
                                type="date"
                                label="Tanggal Keluar / Pindah"
                            />
                            <div class="sm:col-span-2">
                                <BaseInput
                                    v-model="form.keluar_alasan"
                                    label="Alasan Meninggalkan Sekolah / Pindah"
                                    placeholder="Contoh: Mengikuti perpindahan tugas orang tua"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Seksi 4: Keterangan Tambahan -->
                    <BaseInput
                        v-model="form.keterangan_lain_lain"
                        label="Keterangan Tambahan Lain-lain"
                        placeholder="Catatan tambahan mengenai siswa jika ada"
                    />
                </div>
            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-2.5 w-full">
                    <BaseButton variant="secondary" size="sm" @click="closeModal">
                        Batal
                    </BaseButton>
                    <BaseButton
                        variant="primary"
                        size="sm"
                        :loading="form.processing"
                        form="siswaForm"
                        type="submit"
                    >
                        {{ isEditing ? 'Simpan Perubahan' : 'Tambahkan Siswa' }}
                    </BaseButton>
                </div>
            </template>
        </BaseModal>

        <!-- Modal Konfirmasi Hapus Siswa -->
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
