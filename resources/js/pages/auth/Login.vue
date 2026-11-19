<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    Mail,
    Lock,
    Shield,
    UserCheck,
    ArrowRight,
    CheckCircle2,
} from '@lucide/vue';

defineOptions({
    layout: {
        title: 'Masuk Akun',
        description:
            'Silakan masuk untuk mengelola direktori dan nilai buku induk',
    },
});

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};

// Quick fill for testing
const fillCredentials = (role: 'admin' | 'tu') => {
    if (role === 'admin') {
        form.email = 'admin@smk.sch.id';
        form.password = 'password';
    } else {
        form.email = 'tu@smk.sch.id';
        form.password = 'password';
    }
};
</script>

<template>
    <Head title="Masuk Akun - E-Buku Induk" />

    <div class="space-y-5">
        <div
            v-if="status"
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-center text-xs font-semibold text-emerald-800"
        >
            {{ status }}
        </div>

        <!-- Quick Demo Accounts Pill Bar (Gaya modern Vue template) -->
        <div
            class="space-y-2 rounded-2xl border border-blue-100 bg-blue-50/70 p-3 text-xs"
        >
            <div
                class="flex items-center justify-between text-[11px] font-bold tracking-wide text-blue-900 uppercase"
            >
                <span>Pilih Akun Demo Cepat:</span>
                <span class="font-mono text-[10px] text-blue-600"
                    >pwd: password</span
                >
            </div>
            <div class="grid grid-cols-2 gap-2">
                <button
                    type="button"
                    class="flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold text-slate-800 shadow-2xs transition-all hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 active:scale-95"
                    @click="fillCredentials('admin')"
                >
                    <Shield class="h-3.5 w-3.5 text-emerald-600" />
                    <span>Role Admin</span>
                </button>
                <button
                    type="button"
                    class="flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold text-slate-800 shadow-2xs transition-all hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800 active:scale-95"
                    @click="fillCredentials('tu')"
                >
                    <UserCheck class="h-3.5 w-3.5 text-blue-600" />
                    <span>Role Tata Usaha</span>
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email -->
            <div class="space-y-1.5">
                <label
                    for="email"
                    class="block text-xs font-bold text-slate-700"
                >
                    Alamat Email
                </label>
                <div class="relative">
                    <Mail
                        class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="contoh: admin@smk.sch.id"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50/50 py-2.5 pr-3.5 pl-10 text-xs font-medium transition-all placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600"
                        :class="
                            form.errors.email
                                ? 'border-rose-500 bg-rose-50'
                                : ''
                        "
                    />
                </div>
                <p
                    v-if="form.errors.email"
                    class="text-[11px] font-medium text-rose-600"
                >
                    {{ form.errors.email }}
                </p>
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label
                        for="password"
                        class="block text-xs font-bold text-slate-700"
                    >
                        Kata Sandi
                    </label>
                </div>
                <div class="relative">
                    <Lock
                        class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50/50 py-2.5 pr-3.5 pl-10 text-xs font-medium transition-all placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600"
                        :class="
                            form.errors.password
                                ? 'border-rose-500 bg-rose-50'
                                : ''
                        "
                    />
                </div>
                <p
                    v-if="form.errors.password"
                    class="text-[11px] font-medium text-rose-600"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between pt-1">
                <label
                    class="flex cursor-pointer items-center gap-2 select-none"
                >
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="text-xs font-medium text-slate-600"
                        >Ingat sesi saya</span
                    >
                </label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-blue-500/20 transition-all hover:bg-blue-700 active:scale-98 disabled:opacity-50"
            >
                <span v-if="form.processing">Memverifikasi...</span>
                <template v-else>
                    <span>Masuk ke Dashboard</span>
                    <ArrowRight class="h-4 w-4" />
                </template>
            </button>
        </form>
    </div>
</template>
