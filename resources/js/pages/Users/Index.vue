<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    UserCog,
    Plus,
    Edit3,
    Trash2,
    Shield,
    X,
    UserCheck,
    CheckCircle2,
} from '@lucide/vue';

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

const submit = () => {
    if (editingUserId.value) {
        form.put(`/users/${editingUserId.value}`, {
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    } else {
        form.post('/users', {
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    }
};

const deleteUser = (u: UserItem) => {
    if (confirm(`Apakah Anda yakin ingin menghapus user ${u.name}?`)) {
        router.delete(`/users/${u.id}`);
    }
};
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <div class="mx-auto max-w-5xl space-y-6">
        <!-- Header -->
        <div
            class="flex flex-col justify-between gap-4 rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs sm:flex-row sm:items-center"
        >
            <div>
                <h1
                    class="flex items-center gap-2 text-xl font-black tracking-tight text-blue-950 uppercase"
                >
                    <UserCog class="h-6 w-6 text-emerald-600" />
                    Manajemen Pengguna & Hak Akses
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    Kelola akun pengguna dan pembagian hak akses:
                    <strong>Admin</strong> (Akses penuh) dan
                    <strong>Tata Usaha</strong> (Akses operasional).
                </p>
            </div>

            <button
                type="button"
                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-blue-700 active:scale-95"
                @click="openCreate"
            >
                <Plus class="h-4 w-4" />
                <span>+ Tambah Pengguna</span>
            </button>
        </div>

        <!-- Table Users -->
        <div
            class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead
                        class="bg-blue-900 text-[11px] font-bold text-white uppercase"
                    >
                        <tr>
                            <th class="w-12 px-4 py-3 text-center">No</th>
                            <th class="px-4 py-3">Nama Pengguna</th>
                            <th class="px-4 py-3">Email Login</th>
                            <th class="w-36 px-4 py-3 text-center">
                                Role / Peran
                            </th>
                            <th class="w-28 px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr
                            v-for="(u, idx) in users"
                            :key="u.id"
                            class="hover:bg-blue-50/50"
                        >
                            <td
                                class="px-4 py-3 text-center font-bold text-slate-500"
                            >
                                {{ idx + 1 }}
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-900">
                                {{ u.name }}
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-600">
                                {{ u.email }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span
                                    v-if="u.role === 'admin'"
                                    class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800"
                                >
                                    <Shield class="h-3 w-3 text-emerald-600" />
                                    Admin
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-full border border-blue-300 bg-blue-100 px-2.5 py-0.5 text-[11px] font-bold text-blue-800"
                                >
                                    <UserCheck class="h-3 w-3 text-blue-600" />
                                    Tata Usaha
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    class="mr-1 rounded p-1 text-slate-600 hover:bg-blue-50 hover:text-blue-700"
                                    title="Edit"
                                    @click="openEdit(u)"
                                >
                                    <Edit3 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-1 text-slate-400 hover:bg-rose-50 hover:text-rose-600"
                                    title="Hapus"
                                    @click="deleteUser(u)"
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

    <!-- Modal Tambah / Edit User -->
    <div
        v-if="isModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs"
    >
        <div
            class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        >
            <div
                class="flex items-center justify-between bg-blue-900 px-6 py-4 text-white"
            >
                <h3 class="text-sm font-black uppercase">
                    {{
                        editingUserId
                            ? 'Edit Akun Pengguna'
                            : 'Tambah Akun Pengguna Baru'
                    }}
                </h3>
                <button
                    @click="isModalOpen = false"
                    class="p-1 text-blue-200 hover:text-white"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4 p-6 text-xs">
                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Nama Lengkap *</label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Nama Pengguna"
                        class="w-full rounded-lg border px-3 py-2"
                    />
                    <span
                        v-if="form.errors.name"
                        class="text-[10px] text-rose-600"
                        >{{ form.errors.name }}</span
                    >
                </div>

                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Email Login *</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        placeholder="email@sekolah.sch.id"
                        class="w-full rounded-lg border px-3 py-2 font-mono"
                    />
                    <span
                        v-if="form.errors.email"
                        class="text-[10px] text-rose-600"
                        >{{ form.errors.email }}</span
                    >
                </div>

                <div>
                    <label class="mb-1 block font-bold text-slate-700">
                        Password
                        {{
                            editingUserId
                                ? '(Kosongkan jika tidak diubah)'
                                : '*'
                        }}
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        :required="!editingUserId"
                        placeholder="Minimal 8 karakter"
                        class="w-full rounded-lg border px-3 py-2"
                    />
                    <span
                        v-if="form.errors.password"
                        class="text-[10px] text-rose-600"
                        >{{ form.errors.password }}</span
                    >
                </div>

                <div>
                    <label class="mb-1 block font-bold text-slate-700"
                        >Role / Peran Pengguna *</label
                    >
                    <select
                        v-model="form.role"
                        class="w-full rounded-lg border px-3 py-2 font-bold"
                    >
                        <option value="admin">
                            Admin (Akses Penuh + Data Master + User)
                        </option>
                        <option value="tata_usaha">
                            Tata Usaha (Direktori, Buku Induk, Import)
                        </option>
                    </select>
                    <span
                        v-if="form.errors.role"
                        class="text-[10px] text-rose-600"
                        >{{ form.errors.role }}</span
                    >
                </div>

                <div
                    class="flex justify-end gap-2 border-t border-slate-200 pt-3"
                >
                    <button
                        type="button"
                        @click="isModalOpen = false"
                        class="rounded-lg border px-4 py-2"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
