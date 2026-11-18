<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Mail, Lock, Shield, UserCheck, ArrowRight, CheckCircle2 } from '@lucide/vue';

defineOptions({
    layout: {
        title: 'Masuk Akun',
        description: 'Silakan masuk untuk mengelola direktori dan nilai buku induk',
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
            class="p-3 text-center text-xs font-semibold text-emerald-800 bg-emerald-50 rounded-xl border border-emerald-200"
        >
            {{ status }}
        </div>

        <!-- Quick Demo Accounts Pill Bar (Gaya modern Vue template) -->
        <div class="p-3 rounded-2xl bg-blue-50/70 border border-blue-100 text-xs space-y-2">
            <div class="text-[11px] font-bold text-blue-900 uppercase tracking-wide flex items-center justify-between">
                <span>Pilih Akun Demo Cepat:</span>
                <span class="text-[10px] text-blue-600 font-mono">pwd: password</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <button
                    type="button"
                    class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-white hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-slate-800 hover:text-emerald-800 text-[11px] font-bold transition-all shadow-2xs active:scale-95 cursor-pointer"
                    @click="fillCredentials('admin')"
                >
                    <Shield class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Role Admin</span>
                </button>
                <button
                    type="button"
                    class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-white hover:bg-blue-50 border border-slate-200 hover:border-blue-300 text-slate-800 hover:text-blue-800 text-[11px] font-bold transition-all shadow-2xs active:scale-95 cursor-pointer"
                    @click="fillCredentials('tu')"
                >
                    <UserCheck class="w-3.5 h-3.5 text-blue-600" />
                    <span>Role Tata Usaha</span>
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email -->
            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-bold text-slate-700">
                    Alamat Email
                </label>
                <div class="relative">
                    <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="contoh: admin@smk.sch.id"
                        class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all font-medium placeholder:text-slate-400 bg-slate-50/50"
                        :class="form.errors.email ? 'border-rose-500 bg-rose-50' : ''"
                    />
                </div>
                <p v-if="form.errors.email" class="text-[11px] text-rose-600 font-medium">
                    {{ form.errors.email }}
                </p>
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-xs font-bold text-slate-700">
                        Kata Sandi
                    </label>
                </div>
                <div class="relative">
                    <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all font-medium placeholder:text-slate-400 bg-slate-50/50"
                        :class="form.errors.password ? 'border-rose-500 bg-rose-50' : ''"
                    />
                </div>
                <p v-if="form.errors.password" class="text-[11px] text-rose-600 font-medium">
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300"
                    />
                    <span class="text-xs font-medium text-slate-600">Ingat sesi saya</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20 active:scale-98 disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2"
            >
                <span v-if="form.processing">Memverifikasi...</span>
                <template v-else>
                    <span>Masuk ke Dashboard</span>
                    <ArrowRight class="w-4 h-4" />
                </template>
            </button>
        </form>
    </div>
</template>
