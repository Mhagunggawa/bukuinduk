<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Users,
    GraduationCap,
    UserMinus,
    Layers,
    FileSpreadsheet,
    BookOpen,
    ArrowUpRight,
    Search,
    ChevronRight,
} from '@lucide/vue';
import BaseCard from '@/components/base/BaseCard.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseBadge from '@/components/base/BaseBadge.vue';
import BaseTable from '@/components/base/BaseTable.vue';
import EmptyState from '@/components/base/EmptyState.vue';

interface JurusanStat {
    id: number;
    kode: string;
    nama: string;
    total: number;
}

interface RecentSiswa {
    id: number;
    nis: string;
    nisn: string;
    nama_lengkap: string;
    jenis_kelamin: string;
    kelas_saat_ini: string | null;
    status_siswa: 'AKTIF' | 'TAMAT' | 'KELUAR';
    konsentrasi_keahlian?: {
        nama_konsentrasi: string;
        kode_singkat: string;
    };
}

defineProps<{
    stats: {
        totalAktif: number;
        totalTamat: number;
        totalKeluar: number;
        totalSemua: number;
    };
    jurusans: JurusanStat[];
    recentSiswa: RecentSiswa[];
}>();

const studentColumns = [
    { key: 'nis', label: 'NIS / NISN' },
    { key: 'nama', label: 'Nama Lengkap' },
    { key: 'kelas', label: 'Kelas / Jurusan' },
    { key: 'status', label: 'Status', align: 'center' as const },
    { key: 'aksi', label: 'Aksi', align: 'right' as const },
];
</script>

<template>
    <Head title="Dashboard - SI KARANJI" />

    <div class="space-y-6 max-w-7xl mx-auto">
        <!-- Hero Card (Aksen Hiasan Biru Elegan & Tetap Bersih) -->
        <div class="relative bg-gradient-to-br from-white via-[#F8FBFE] to-[#EEF6FC] dark:from-[#131F37] dark:via-[#162544] dark:to-[#1A2C50] rounded-2xl border border-[#BFE3FA]/80 dark:border-[#223354] p-6 sm:p-7 shadow-xs overflow-hidden transition-all duration-300">
            <!-- Hiasan Cahaya Biru Halus (Ambient Glow) di Latar Belakang -->
            <div class="pointer-events-none absolute -top-16 -right-16 w-64 h-64 rounded-full bg-[#2B7CC2]/10 dark:bg-[#38BDF8]/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 right-1/4 w-48 h-48 rounded-full bg-[#BFE3FA]/35 dark:bg-[#1E3A5F]/30 blur-2xl"></div>

            <!-- Konten Utama Card -->
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-2.5 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EAF4FC] dark:bg-[#1E3A5F]/60 text-xs font-semibold text-[#2B7CC2] dark:text-[#38BDF8] border border-[#BFE3FA] dark:border-[#2B7CC2]/40 shadow-2xs select-none">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2B7CC2] dark:bg-[#38BDF8]"></span>
                        <span>SI KARANJI SMK</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#0F3A5F] dark:text-[#F1F5F9]">
                        Sistem Informasi Buku Induk Siswa SMK
                    </h1>
                    <p class="text-xs sm:text-sm text-[#64748B] dark:text-[#94A3B8] leading-relaxed">
                        Pencatatan siklus hidup siswa, rekapitulasi nilai akademik Semester I s.d. X, ekstrakurikuler, absensi, serta arsip digital pasfoto dan mutasi.
                    </p>
                </div>

                <div class="flex items-center gap-4 shrink-0">
                    <!-- Maskot dengan Aksen Glow Biru Lembut -->
                    <div class="relative flex items-center justify-center">
                        <div class="absolute inset-0 bg-[#2B7CC2]/15 dark:bg-[#38BDF8]/15 rounded-full blur-xl scale-110"></div>
                        <img
                            src="/images/sikaranji-card.webp"
                            alt="Maskot SI KARANJI"
                            class="relative z-10 w-20 h-20 sm:w-24 sm:h-24 object-contain drop-shadow-xs"
                            loading="eager"
                        />
                    </div>

                    <BaseButton href="/siswa" variant="primary" size="md" class="shadow-xs hover:shadow-md transition-shadow">
                        <Search class="w-4 h-4 mr-1.5" />
                        <span>Buka Direktori Siswa</span>
                    </BaseButton>
                </div>
            </div>
        </div>

        <!-- 4 Metric Cards (Desain Premium, Modern & Clean) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <!-- 1. Total Siswa Aktif -->
            <Link
                href="/siswa"
                class="group relative bg-white dark:bg-[#131F37] rounded-2xl border border-slate-200/90 dark:border-[#223354] p-5 shadow-xs hover:shadow-md hover:border-[#2B7CC2]/40 dark:hover:border-[#38BDF8]/40 hover:-translate-y-0.5 transition-all duration-300 block overflow-hidden"
            >
                <!-- Soft Ambient Glow -->
                <div class="pointer-events-none absolute -top-8 -right-8 w-24 h-24 rounded-full bg-emerald-500/10 dark:bg-emerald-500/15 blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>

                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-[#94A3B8]">
                        Siswa Aktif
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100/80 dark:border-emerald-800/40 shadow-2xs group-hover:scale-105 transition-transform">
                        <Users class="w-5 h-5" />
                    </div>
                </div>

                <div class="mt-3 flex items-baseline gap-2.5">
                    <span class="text-3xl font-black text-[#0F3A5F] dark:text-[#F1F5F9] tracking-tight group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] transition-colors">
                        {{ stats.totalAktif }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/50">
                        Aktif
                    </span>
                </div>

                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-[#223354] flex items-center justify-between text-xs text-slate-500 dark:text-[#94A3B8]">
                    <div class="flex items-center gap-1.5 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="truncate">Terdaftar di rombel berjalan</span>
                    </div>
                    <ArrowUpRight class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all shrink-0" />
                </div>
            </Link>

            <!-- 2. Total Jurusan (Konsentrasi Keahlian) -->
            <Link
                href="/master-data"
                class="group relative bg-white dark:bg-[#131F37] rounded-2xl border border-slate-200/90 dark:border-[#223354] p-5 shadow-xs hover:shadow-md hover:border-[#2B7CC2]/40 dark:hover:border-[#38BDF8]/40 hover:-translate-y-0.5 transition-all duration-300 block overflow-hidden"
            >
                <!-- Soft Ambient Glow -->
                <div class="pointer-events-none absolute -top-8 -right-8 w-24 h-24 rounded-full bg-blue-500/10 dark:bg-blue-500/15 blur-2xl group-hover:bg-blue-500/20 transition-all"></div>

                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-[#94A3B8]">
                        Konsentrasi Keahlian
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-[#1E3A5F]/40 text-[#2B7CC2] dark:text-[#38BDF8] flex items-center justify-center border border-blue-100/80 dark:border-[#2B7CC2]/40 shadow-2xs group-hover:scale-105 transition-transform">
                        <Layers class="w-5 h-5" />
                    </div>
                </div>

                <div class="mt-3 flex items-baseline gap-2.5">
                    <span class="text-3xl font-black text-[#0F3A5F] dark:text-[#F1F5F9] tracking-tight group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] transition-colors">
                        {{ jurusans.length }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#EAF4FC] dark:bg-[#1E3A5F]/60 text-[#2B7CC2] dark:text-[#38BDF8] border border-[#BFE3FA] dark:border-[#2B7CC2]/50">
                        Jurusan
                    </span>
                </div>

                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-[#223354] flex items-center justify-between text-xs text-slate-500 dark:text-[#94A3B8]">
                    <div class="flex items-center gap-1.5 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2B7CC2] dark:bg-[#38BDF8] shrink-0"></span>
                        <span class="truncate">Konsentrasi keahlian aktif</span>
                    </div>
                    <ArrowUpRight class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all shrink-0" />
                </div>
            </Link>

            <!-- 3. Siswa Tamat / Lulus -->
            <Link
                href="/siswa"
                class="group relative bg-white dark:bg-[#131F37] rounded-2xl border border-slate-200/90 dark:border-[#223354] p-5 shadow-xs hover:shadow-md hover:border-[#2B7CC2]/40 dark:hover:border-[#38BDF8]/40 hover:-translate-y-0.5 transition-all duration-300 block overflow-hidden"
            >
                <!-- Soft Ambient Glow -->
                <div class="pointer-events-none absolute -top-8 -right-8 w-24 h-24 rounded-full bg-indigo-500/10 dark:bg-indigo-500/15 blur-2xl group-hover:bg-indigo-500/20 transition-all"></div>

                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-[#94A3B8]">
                        Siswa Tamat / Lulus
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-100/80 dark:border-indigo-800/40 shadow-2xs group-hover:scale-105 transition-transform">
                        <GraduationCap class="w-5 h-5" />
                    </div>
                </div>

                <div class="mt-3 flex items-baseline gap-2.5">
                    <span class="text-3xl font-black text-[#0F3A5F] dark:text-[#F1F5F9] tracking-tight group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] transition-colors">
                        {{ stats.totalTamat }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/50">
                        Alumni
                    </span>
                </div>

                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-[#223354] flex items-center justify-between text-xs text-slate-500 dark:text-[#94A3B8]">
                    <div class="flex items-center gap-1.5 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                        <span class="truncate">Telah menyelesaikan studi</span>
                    </div>
                    <ArrowUpRight class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all shrink-0" />
                </div>
            </Link>

            <!-- 4. Siswa Keluar / Mutasi -->
            <Link
                href="/siswa"
                class="group relative bg-white dark:bg-[#131F37] rounded-2xl border border-slate-200/90 dark:border-[#223354] p-5 shadow-xs hover:shadow-md hover:border-rose-400/40 dark:hover:border-rose-400/40 hover:-translate-y-0.5 transition-all duration-300 block overflow-hidden"
            >
                <!-- Soft Ambient Glow -->
                <div class="pointer-events-none absolute -top-8 -right-8 w-24 h-24 rounded-full bg-rose-500/10 dark:bg-rose-500/15 blur-2xl group-hover:bg-rose-500/20 transition-all"></div>

                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-[#94A3B8]">
                        Siswa Keluar / Mutasi
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center border border-rose-100/80 dark:border-rose-800/40 shadow-2xs group-hover:scale-105 transition-transform">
                        <UserMinus class="w-5 h-5" />
                    </div>
                </div>

                <div class="mt-3 flex items-baseline gap-2.5">
                    <span class="text-3xl font-black text-[#0F3A5F] dark:text-[#F1F5F9] tracking-tight group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">
                        {{ stats.totalKeluar }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/50">
                        Mutasi
                    </span>
                </div>

                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-[#223354] flex items-center justify-between text-xs text-slate-500 dark:text-[#94A3B8]">
                    <div class="flex items-center gap-1.5 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                        <span class="truncate">Pindah atau keluar sekolah</span>
                    </div>
                    <ArrowUpRight class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 group-hover:text-rose-500 dark:group-hover:text-rose-400 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all shrink-0" />
                </div>
            </Link>
        </div>

        <!-- Pintasan Cepat -->
        <BaseCard title="Pintasan Operasional" subtitle="Akses langsung ke menu utama sistem">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <Link
                    href="/import"
                    class="p-4 rounded-[8px] border border-[#E2E8F0] dark:border-[#223354] hover:border-[#BFE3FA] dark:hover:border-[#38BDF8]/40 hover:bg-[#EAF4FC]/40 dark:hover:bg-[#1E293B]/60 transition-colors flex items-start gap-3.5 group"
                >
                    <div class="w-10 h-10 rounded-[8px] bg-[#EAF4FC] dark:bg-[#1E3A5F]/50 text-[#2B7CC2] dark:text-[#38BDF8] flex items-center justify-center shrink-0 border border-[#BFE3FA]/60 dark:border-[#2B7CC2]/40">
                        <FileSpreadsheet class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-[#0F3A5F] dark:text-[#F1F5F9] group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] flex items-center gap-1 transition-colors">
                            Import Identitas Excel
                            <ArrowUpRight class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8]" />
                        </div>
                        <p class="text-[11px] text-[#64748B] dark:text-[#94A3B8] mt-0.5 leading-snug">
                            Unggah data siswa baru massal format baku
                        </p>
                    </div>
                </Link>

                <Link
                    href="/import"
                    class="p-4 rounded-[8px] border border-[#E2E8F0] dark:border-[#223354] hover:border-[#BFE3FA] dark:hover:border-[#38BDF8]/40 hover:bg-[#EAF4FC]/40 dark:hover:bg-[#1E293B]/60 transition-colors flex items-start gap-3.5 group"
                >
                    <div class="w-10 h-10 rounded-[8px] bg-[#EAF4FC] dark:bg-[#1E3A5F]/50 text-[#2B7CC2] dark:text-[#38BDF8] flex items-center justify-center shrink-0 border border-[#BFE3FA]/60 dark:border-[#2B7CC2]/40">
                        <BookOpen class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-[#0F3A5F] dark:text-[#F1F5F9] group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] flex items-center gap-1 transition-colors">
                            Import Nilai Semester
                            <ArrowUpRight class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8]" />
                        </div>
                        <p class="text-[11px] text-[#64748B] dark:text-[#94A3B8] mt-0.5 leading-snug">
                            Rekap nilai akademik, ekskul & absensi
                        </p>
                    </div>
                </Link>

                <Link
                    href="/siswa"
                    class="p-4 rounded-[8px] border border-[#E2E8F0] dark:border-[#223354] hover:border-[#BFE3FA] dark:hover:border-[#38BDF8]/40 hover:bg-[#EAF4FC]/40 dark:hover:bg-[#1E293B]/60 transition-colors flex items-start gap-3.5 group"
                >
                    <div class="w-10 h-10 rounded-[8px] bg-[#EAF4FC] dark:bg-[#1E3A5F]/50 text-[#2B7CC2] dark:text-[#38BDF8] flex items-center justify-center shrink-0 border border-[#BFE3FA]/60 dark:border-[#2B7CC2]/40">
                        <Search class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-[#0F3A5F] dark:text-[#F1F5F9] group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] flex items-center gap-1 transition-colors">
                            Buku Induk Digital
                            <ArrowUpRight class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8]" />
                        </div>
                        <p class="text-[11px] text-[#64748B] dark:text-[#94A3B8] mt-0.5 leading-snug">
                            Kelola lembar profil dan cetak format F4/A3
                        </p>
                    </div>
                </Link>
            </div>
        </BaseCard>

        <!-- Persebaran Jurusan & Siswa Terkini -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Persebaran Jurusan -->
            <BaseCard
                title="Persebaran Jurusan"
                subtitle="Jumlah siswa aktif per konsentrasi"
                class="flex flex-col"
            >
                <template #header-actions>
                    <BaseBadge variant="primary" size="sm">
                        {{ jurusans.length }} Jurusan
                    </BaseBadge>
                </template>

                <div class="space-y-2">
                    <div
                        v-for="jurusan in jurusans"
                        :key="jurusan.id"
                        class="p-2.5 rounded-[8px] border border-[#E2E8F0] dark:border-[#223354] bg-[#F8FAFC] dark:bg-[#0B1120] flex items-center justify-between"
                    >
                        <div class="min-w-0 pr-2">
                            <div class="font-bold text-xs text-[#0F3A5F] dark:text-[#F1F5F9] truncate">
                                {{ jurusan.nama }}
                            </div>
                            <div class="text-[11px] text-[#64748B] dark:text-[#94A3B8] font-medium mt-0.5">
                                Kode: <span class="text-[#2B7CC2] dark:text-[#38BDF8] font-mono">{{ jurusan.kode }}</span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-sm font-bold text-[#0F3A5F] dark:text-[#F1F5F9]">{{ jurusan.total }}</span>
                            <span class="text-[10px] text-[#64748B] dark:text-[#94A3B8] block leading-none">siswa</span>
                        </div>
                    </div>

                    <EmptyState
                        v-if="jurusans.length === 0"
                        title="Belum ada jurusan"
                        description="Data konsentrasi keahlian belum tersedia."
                    />
                </div>
            </BaseCard>

            <!-- Siswa Diperbarui Terkini -->
            <BaseCard
                title="Siswa Terkini"
                subtitle="Pembaruan biodata atau nilai terakhir"
                class="lg:col-span-2 flex flex-col"
            >
                <template #header-actions>
                    <Link
                        href="/siswa"
                        class="text-xs font-semibold text-[#2B7CC2] dark:text-[#38BDF8] hover:underline flex items-center gap-1"
                    >
                        <span>Direktori Lengkap</span>
                        <ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                </template>

                <BaseTable :columns="studentColumns" :is-empty="recentSiswa.length === 0">
                    <tr
                        v-for="s in recentSiswa"
                        :key="s.id"
                        class="hover:bg-[#F8FAFC] dark:hover:bg-[#1E293B]/60 transition-colors"
                    >
                        <td class="px-4 py-2.5 font-mono text-xs font-medium text-[#64748B] dark:text-[#94A3B8] whitespace-nowrap">
                            {{ s.nis }} / {{ s.nisn }}
                        </td>
                        <td class="px-4 py-2.5 font-semibold text-[#0F3A5F] dark:text-[#F1F5F9] whitespace-nowrap">
                            {{ s.nama_lengkap }}
                        </td>
                        <td class="px-4 py-2.5 text-[#64748B] dark:text-[#94A3B8] whitespace-nowrap text-xs">
                            {{ s.kelas_saat_ini || '-' }}
                            <span v-if="s.konsentrasi_keahlian" class="text-slate-400 dark:text-slate-500">
                                ({{ s.konsentrasi_keahlian.kode_singkat }})
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-center whitespace-nowrap">
                            <BaseBadge
                                v-if="s.status_siswa === 'AKTIF'"
                                variant="success"
                                size="sm"
                                dot
                            >
                                AKTIF
                            </BaseBadge>
                            <BaseBadge
                                v-else-if="s.status_siswa === 'TAMAT'"
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
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <BaseButton
                                :href="`/siswa/${s.id}/buku-induk`"
                                variant="outline"
                                size="sm"
                            >
                                <BookOpen class="w-3.5 h-3.5" />
                                <span>Buku Induk</span>
                            </BaseButton>
                        </td>
                    </tr>

                    <template #empty>
                        <EmptyState
                            title="Belum ada data siswa"
                            description="Siswa yang baru didaftarkan atau diperbarui akan muncul di sini."
                        />
                    </template>
                </BaseTable>
            </BaseCard>
        </div>
    </div>
</template>
