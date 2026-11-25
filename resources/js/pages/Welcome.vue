<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Printer,
    BookOpen,
    FileSpreadsheet,
    ShieldCheck,
} from '@lucide/vue';
import BaseButton from '@/components/base/BaseButton.vue';
</script>

<template>
    <Head title="SI KARANJI - Buku Induk Siswa SMK" />

    <div class="min-h-screen bg-[#F8FAFC] text-[#0F3A5F] flex flex-col justify-between p-6 sm:p-12 lg:p-16 relative overflow-hidden font-sans antialiased selection:bg-[#EAF4FC] selection:text-[#2B7CC2]">
        <!-- Blueprint Grid Pattern with Soft Vignette Mask -->
        <div
            class="pointer-events-none absolute inset-0 opacity-40"
            style="background-image: linear-gradient(to right, #CBD5E1 1px, transparent 1px), linear-gradient(to bottom, #CBD5E1 1px, transparent 1px); background-size: 32px 32px; mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%); -webkit-mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);"
        ></div>

        <!-- Ambient Background Glow Accents -->
        <div class="pointer-events-none absolute -top-32 -right-32 w-[34rem] h-[34rem] rounded-full bg-[#EAF4FC] blur-3xl opacity-60"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-32 w-[34rem] h-[34rem] rounded-full bg-[#EAF4FC] blur-3xl opacity-50"></div>

        <div class="hidden sm:block"></div>

        <!-- Main Hero Section -->
        <main class="max-w-6xl w-full mx-auto my-auto py-8 sm:py-12 z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                <!-- SISI KIRI: Headline, Identitas & Tombol Login Tunggal -->
                <div class="lg:col-span-6 space-y-6 text-left">
                    <!-- Status Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-[#BFE3FA] shadow-xs">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#2B7CC2] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#2B7CC2]"></span>
                        </span>
                        <span class="text-[11px] font-bold text-[#2B7CC2] tracking-wider uppercase">
                            ARSIP DIGITAL BUKU INDUK SMK
                        </span>
                    </div>

                    <!-- Judul Utama & Sub-identitas -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <h1 class="text-4xl sm:text-5xl lg:text-[56px] font-black text-[#0F3A5F] tracking-tight leading-none">
                                SI KARANJI
                            </h1>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-[#2B7CC2] leading-tight">
                            Kelola Arsip, Rekap Administrasi, Nilai, dan Jejak Induk.
                        </h2>
                    </div>

                    <!-- Deskripsi Rapi & Bermakna -->
                    <p class="text-sm sm:text-base text-[#64748B] leading-relaxed max-w-lg">
                        Dalam bahasa Gorontalo, kopiah keranjang disebut “Upiya Karanji”. Peci itu dianyam rapi helai demi helai, mirip data siswa yang disusun dan dijaga rapi di buku induk. 
                    </p>

                    <!-- Feature Tags Inovatif (Mini Pills) -->
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-xs font-semibold text-[#0F3A5F] shadow-2xs">
                            <BookOpen class="w-3.5 h-3.5 text-[#2B7CC2]" />
                            Matriks 10 Semester
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-xs font-semibold text-[#0F3A5F] shadow-2xs">
                            <Printer class="w-3.5 h-3.5 text-[#2B7CC2]" />
                            Format Baku F4 / A3
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] text-xs font-semibold text-[#0F3A5F] shadow-2xs">
                            <FileSpreadsheet class="w-3.5 h-3.5 text-[#2B7CC2]" />
                            Sinkronisasi Excel Dapodik
                        </span>
                    </div>

                    <!-- Tombol Login Tunggal (Satu-satunya Tombol Aksi) -->
                    <div class="space-y-3 pt-3 border-t border-[#E2E8F0]">
                        <div class="flex flex-wrap items-center gap-3">
                            <Link :href="$page.props.auth?.user ? '/dashboard' : '/login'">
                                <BaseButton variant="primary" size="lg" class="shadow-sm px-6 py-3 rounded-xl text-sm font-bold">
                                    <span>{{ $page.props.auth?.user ? 'Buka Dashboard Sistem' : 'Login ke SI KARANJI' }}</span>
                                    <ArrowRight class="w-4 h-4 ml-2" />
                                </BaseButton>
                            </Link>
                        </div>

                        <p class="text-[11px] text-[#64748B] flex items-center gap-1.5">
                            <ShieldCheck class="w-3.5 h-3.5 text-[#2B7CC2] shrink-0" />
                            Portal resmi operator Admin & staf Tata Usaha SMK.
                        </p>
                    </div>
                </div>

                <!-- SISI KANAN: Logo Besar & Tagline Filosofis -->
                <div class="lg:col-span-6 flex flex-col justify-center lg:justify-end items-center">
                    <div class="relative w-full max-w-md flex flex-col items-center justify-center p-2">
                        <img
                            src="/images/logo-full.webp"
                            alt="Logo Maskot SI KARANJI"
                            class="w-full max-w-[340px] sm:max-w-[380px] lg:max-w-[400px] h-auto object-contain select-none transition-transform duration-300 hover:scale-[1.02]"
                        />

                        <!-- Tagline di bawah logo -->
                        <div class="mt-4 sm:mt-5 inline-flex items-center gap-2.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-white/95 backdrop-blur-xs border border-[#E2E8F0] shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-[#2B7CC2] shrink-0 animate-pulse"></span>
                            <p class="text-xs sm:text-sm font-semibold text-[#0F3A5F] italic tracking-tight">
                                &ldquo;Menganyam data induk, menjaga jejak siswa.&rdquo;
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer Minimalis -->
        <footer class="max-w-6xl w-full mx-auto text-center text-xs text-[#64748B] z-10 pt-4">
            &copy; {{ new Date().getFullYear() }} SI KARANJI • Sistem Informasi Buku Induk Siswa SMK.
        </footer>
    </div>
</template>
