<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    UserCog,
    Plus,
    Edit3,
    Trash2,
    Shield,
    UserCheck,
    Users,
} from '@lucide/vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseCard from '@/components/base/BaseCard.vue';
import BaseInput from '@/components/base/BaseInput.vue';
import BaseSelect from '@/components/base/BaseSelect.vue';
import BaseTable from '@/components/base/BaseTable.vue';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseConfirmModal from '@/components/base/BaseConfirmModal.vue';
import BaseBadge from '@/components/base/BaseBadge.vue';
import EmptyState from '@/components/base/EmptyState.vue';
import { useConfirmModal } from '@/composables/useConfirmModal';

interface UserItem {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'tata_usaha';
    created_at: string;
}

const props = defineProps<{
    users: UserItem[];
}>();

const isModalOpen = ref(false);
const editingUserId = ref<number | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'tata_usaha' as 'admin' | 'tata_usaha',
});

const openCreate = () => {
    editingUserId.value = null;
    form.reset();
    form.clearErrors();
    form.role = 'tata_usaha';
    isModalOpen.value = true;
};

const openEdit = (u: UserItem) => {
    editingUserId.value = u.id;
    form.reset();
    form.clearErrors();
    form.name = u.name;
    form.email = u.email;
    form.password = '';
    form.role = u.role;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingUserId.value = null;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (editingUserId.value) {
        form.put(`/users/${editingUserId.value}`, {
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        form.post('/users', {
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

const deleteUser = (u: UserItem) => {
    openConfirm({
        title: 'Hapus Akun Pengguna',
        message: 'Akun ini akan dihapus dan tidak dapat digunakan lagi untuk masuk ke sistem SI KARANJI.',
        itemInfo: u.name,
        subInfo: `${u.email} • Role: ${u.role === 'admin' ? 'Administrator' : 'Tata Usaha'}`,
        confirmText: 'Ya, Hapus Akun',
        variant: 'danger',
        onConfirm: () => {
            router.delete(`/users/${u.id}`);
        },
    });
};

const roleOptions = [
    { value: 'admin', label: 'Admin (Akses Penuh + Data Master + User)' },
    { value: 'tata_usaha', label: 'Tata Usaha (Direktori, Buku Induk, Import)' },
];

const userColumns = [
    { key: 'no', label: 'No', width: '3.5rem', align: 'center' as const },
    { key: 'name', label: 'Nama Pengguna' },
    { key: 'email', label: 'Email Login' },
    { key: 'role', label: 'Role / Peran', width: '10rem', align: 'center' as const },
    { key: 'action', label: 'Aksi', width: '7rem', align: 'right' as const },
];
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <div class="space-y-6 max-w-5xl mx-auto">
        <!-- Header -->
        <BaseCard class="p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-[#EAF4FC] text-[#2B7CC2] flex items-center justify-center shrink-0">
                        <UserCog class="w-5 h-5" />
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-[#0F3A5F]">
                            Manajemen Pengguna & Hak Akses
                        </h1>
                        <p class="text-xs text-[#64748B] mt-0.5">
                            Kelola akun operator sistem: <strong>Admin</strong> (akses penuh) dan <strong>Tata Usaha</strong> (akses operasional direktori & nilai).
                        </p>
                    </div>
                </div>

                <BaseButton
                    variant="primary"
                    size="sm"
                    @click="openCreate"
                >
                    <Plus class="w-4 h-4 mr-1.5" />
                    Tambah Pengguna
                </BaseButton>
            </div>
        </BaseCard>

        <!-- Table Users -->
        <BaseTable :columns="userColumns" :is-empty="users.length === 0">
            <tr
                v-for="(u, idx) in users"
                :key="u.id"
                class="hover:bg-[#F8FAFC] dark:hover:bg-[#1A2846] transition-colors"
            >
                <td class="px-4 py-3 text-center font-medium text-[#64748B] dark:text-[#94A3B8]">
                    {{ idx + 1 }}
                </td>
                <td class="px-4 py-3 font-semibold text-[#0F3A5F] dark:text-[#F1F5F9]">
                    {{ u.name }}
                </td>
                <td class="px-4 py-3 text-[#64748B] dark:text-[#94A3B8] font-mono text-xs">
                    {{ u.email }}
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <BaseBadge
                        v-if="u.role === 'admin'"
                        variant="success"
                        size="sm"
                        dot
                    >
                        Admin
                    </BaseBadge>
                    <BaseBadge
                        v-else
                        variant="primary"
                        size="sm"
                        dot
                    >
                        Tata Usaha
                    </BaseBadge>
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <button
                        type="button"
                        class="p-1.5 rounded-md text-[#64748B] dark:text-[#94A3B8] hover:text-[#2B7CC2] dark:hover:text-[#38BDF8] hover:bg-[#EAF4FC] dark:hover:bg-[#223354] transition-colors mr-1 cursor-pointer"
                        aria-label="Edit akun pengguna"
                        title="Edit"
                        @click="openEdit(u)"
                    >
                        <Edit3 class="w-4 h-4" />
                    </button>
                    <button
                        type="button"
                        class="p-1.5 rounded-md text-[#64748B] dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                        aria-label="Hapus akun pengguna"
                        title="Hapus"
                        @click="deleteUser(u)"
                    >
                        <Trash2 class="w-4 h-4" />
                    </button>
                </td>
            </tr>

            <template #empty>
                <EmptyState
                    title="Belum ada pengguna"
                    description="Tambahkan pengguna pertama untuk memberikan hak akses kepada petugas sekolah."
                >
                    <template #icon>
                        <Users class="w-6 h-6 text-[#2B7CC2]" />
                    </template>
                    <template #action>
                        <BaseButton variant="primary" size="sm" @click="openCreate">
                            <Plus class="w-4 h-4 mr-1.5" />
                            Tambah Pengguna
                        </BaseButton>
                    </template>
                </EmptyState>
            </template>
        </BaseTable>

        <!-- Modal Tambah / Edit User -->
        <BaseModal
            :open="isModalOpen"
            :title="editingUserId ? 'Edit Akun Pengguna' : 'Tambah Akun Pengguna Baru'"
            size="md"
            @close="closeModal"
        >
            <form id="userForm" @submit.prevent="submit" class="space-y-4">
                <BaseInput
                    v-model="form.name"
                    label="Nama Lengkap"
                    placeholder="Nama Pengguna"
                    required
                    :error="form.errors.name"
                />

                <BaseInput
                    v-model="form.email"
                    type="email"
                    label="Email Login"
                    placeholder="email@sekolah.sch.id"
                    required
                    :error="form.errors.email"
                />

                <BaseInput
                    v-model="form.password"
                    type="password"
                    :label="editingUserId ? 'Password (Kosongkan jika tidak diubah)' : 'Password'"
                    placeholder="Minimal 8 karakter"
                    :required="!editingUserId"
                    :error="form.errors.password"
                />

                <BaseSelect
                    v-model="form.role"
                    label="Role / Peran Pengguna"
                    :options="roleOptions"
                    required
                    :error="form.errors.role"
                />
            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-2 w-full">
                    <BaseButton
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="closeModal"
                    >
                        Batal
                    </BaseButton>
                    <BaseButton
                        type="submit"
                        form="userForm"
                        variant="primary"
                        size="sm"
                        :loading="form.processing"
                        @click="submit"
                    >
                        Simpan Pengguna
                    </BaseButton>
                </div>
            </template>
        </BaseModal>

        <!-- Modal Konfirmasi Hapus Akun -->
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
