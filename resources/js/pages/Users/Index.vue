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

    <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="bg-white p-6 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-blue-950 uppercase tracking-tight flex items-center gap-2">
                        <UserCog class="w-6 h-6 text-emerald-600" />
                        Manajemen Pengguna & Hak Akses
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Kelola akun pengguna dan pembagian hak akses: <strong>Admin</strong> (Akses penuh) dan <strong>Tata Usaha</strong> (Akses operasional).
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-xs active:scale-95 cursor-pointer"
                    @click="openCreate"
                >
                    <Plus class="w-4 h-4" />
                    <span>+ Tambah Pengguna</span>
                </button>
            </div>

            <!-- Table Users -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-blue-900 text-white uppercase text-[11px] font-bold">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">No</th>
                                <th class="px-4 py-3">Nama Pengguna</th>
                                <th class="px-4 py-3">Email Login</th>
                                <th class="px-4 py-3 text-center w-36">Role / Peran</th>
                                <th class="px-4 py-3 text-right w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr
                                v-for="(u, idx) in users"
                                :key="u.id"
                                class="hover:bg-blue-50/50"
                            >
                                <td class="px-4 py-3 text-center font-bold text-slate-500">{{ idx + 1 }}</td>
                                <td class="px-4 py-3 font-bold text-slate-900">{{ u.name }}</td>
                                <td class="px-4 py-3 text-slate-600 font-mono">{{ u.email }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span
                                        v-if="u.role === 'admin'"
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300"
                                    >
                                        <Shield class="w-3 h-3 text-emerald-600" />
                                        Admin
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-300"
                                    >
                                        <UserCheck class="w-3 h-3 text-blue-600" />
                                        Tata Usaha
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        class="p-1 rounded text-slate-600 hover:text-blue-700 hover:bg-blue-50 mr-1"
                                        title="Edit"
                                        @click="openEdit(u)"
                                    >
                                        <Edit3 class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                        title="Hapus"
                                        @click="deleteUser(u)"
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

        <!-- Modal Tambah / Edit User -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 bg-blue-900 text-white flex items-center justify-between">
                    <h3 class="font-black text-sm uppercase">
                        {{ editingUserId ? 'Edit Akun Pengguna' : 'Tambah Akun Pengguna Baru' }}
                    </h3>
                    <button @click="isModalOpen = false" class="text-blue-200 hover:text-white p-1">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input v-model="form.name" type="text" required placeholder="Nama Pengguna" class="w-full px-3 py-2 border rounded-lg" />
                        <span v-if="form.errors.name" class="text-rose-600 text-[10px]">{{ form.errors.name }}</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Email Login *</label>
                        <input v-model="form.email" type="email" required placeholder="email@sekolah.sch.id" class="w-full px-3 py-2 border rounded-lg font-mono" />
                        <span v-if="form.errors.email" class="text-rose-600 text-[10px]">{{ form.errors.email }}</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Password {{ editingUserId ? '(Kosongkan jika tidak diubah)' : '*' }}
                        </label>
                        <input v-model="form.password" type="password" :required="!editingUserId" placeholder="Minimal 8 karakter" class="w-full px-3 py-2 border rounded-lg" />
                        <span v-if="form.errors.password" class="text-rose-600 text-[10px]">{{ form.errors.password }}</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Role / Peran Pengguna *</label>
                        <select v-model="form.role" class="w-full px-3 py-2 border rounded-lg font-bold">
                            <option value="admin">Admin (Akses Penuh + Data Master + User)</option>
                            <option value="tata_usaha">Tata Usaha (Direktori, Buku Induk, Import)</option>
                        </select>
                        <span v-if="form.errors.role" class="text-rose-600 text-[10px]">{{ form.errors.role }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border rounded-lg">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
</template>
