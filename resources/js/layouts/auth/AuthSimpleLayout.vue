<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { GraduationCap } from '@lucide/vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/40 to-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans antialiased text-slate-900 relative overflow-hidden">
        <!-- Modern decorative background blur circles -->
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="flex flex-col items-center text-center">
                <!-- Modern Logo Badge -->
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-900 to-blue-600 flex items-center justify-center text-white shadow-xl shadow-blue-500/25 ring-4 ring-white mb-3">
                    <GraduationCap class="w-8 h-8" />
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                    E-BUKU INDUK SISWA
                </h2>
                <p class="text-xs font-semibold text-blue-700 uppercase tracking-widest mt-0.5">
                    Sistem Manajemen Akademik & Prestasi SMK
                </p>
            </div>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
            <div class="bg-white/95 backdrop-blur-md py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-200/80">
                <div class="mb-6 text-center">
                    <h3 class="text-base font-bold text-slate-900">{{ title || 'Masuk ke Sistem' }}</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ description || 'Masukkan alamat email dan kata sandi Anda' }}
                    </p>
                </div>
                <slot />
            </div>

            <!-- Footer copyright -->
            <div class="text-center mt-6 text-xs text-slate-500 font-medium">
                &copy; {{ new Date().getFullYear() }} E-Buku Induk SMK. Clean & Modern Architecture.
            </div>
        </div>
    </div>
</template>
