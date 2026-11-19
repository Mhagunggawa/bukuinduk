<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { GraduationCap } from '@lucide/vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-blue-50/40 to-slate-100 py-12 font-sans text-slate-900 antialiased sm:px-6 lg:px-8"
    >
        <!-- Modern decorative background blur circles -->
        <div
            class="pointer-events-none absolute -top-32 -left-32 h-96 w-96 rounded-full bg-blue-400/10 blur-3xl"
        ></div>
        <div
            class="pointer-events-none absolute -right-32 -bottom-32 h-96 w-96 rounded-full bg-indigo-500/10 blur-3xl"
        ></div>

        <div class="relative z-10 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex flex-col items-center text-center">
                <!-- Modern Logo Badge -->
                <div
                    class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-900 to-blue-600 text-white shadow-xl ring-4 shadow-blue-500/25 ring-white"
                >
                    <GraduationCap class="h-8 w-8" />
                </div>
                <h2 class="text-2xl font-black tracking-tight text-slate-900">
                    E-BUKU INDUK SISWA
                </h2>
                <p
                    class="mt-0.5 text-xs font-semibold tracking-widest text-blue-700 uppercase"
                >
                    Sistem Manajemen Akademik & Prestasi SMK
                </p>
            </div>
        </div>

        <div
            class="relative z-10 mt-6 px-4 sm:mx-auto sm:w-full sm:max-w-md sm:px-0"
        >
            <div
                class="rounded-3xl border border-slate-200/80 bg-white/95 px-6 py-8 shadow-xl shadow-slate-200/60 backdrop-blur-md sm:px-10"
            >
                <div class="mb-6 text-center">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ title || 'Masuk ke Sistem' }}
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">
                        {{
                            description ||
                            'Masukkan alamat email dan kata sandi Anda'
                        }}
                    </p>
                </div>
                <slot />
            </div>

            <!-- Footer copyright -->
            <div class="mt-6 text-center text-xs font-medium text-slate-500">
                &copy; {{ new Date().getFullYear() }} E-Buku Induk SMK. Clean &
                Modern Architecture.
            </div>
        </div>
    </div>
</template>
