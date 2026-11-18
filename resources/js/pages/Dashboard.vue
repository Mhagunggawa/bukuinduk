<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
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
    CheckCircle2,
    Calendar,
    Sparkles,
} from '@lucide/vue';

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
    activeTahun?: {
        tahun_pelajaran: string;
    };
}>();
</script>

<template>
    <Head title="Dashboard - E-Buku Induk" />

        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Modern Hero Banner (Gaya Modern Vue Template) -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0B1736] via-blue-900 to-indigo-950 p-6 sm:p-8 text-white shadow-xl border border-slate-800/40">
                <!-- Decorative Blur Circles -->
                <div class="absolute -right-16 -top-16 w-72 h-72 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute right-32 -bottom-16 w-60 h-60 bg-sky-400/15 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-2 max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-sky-300 ring-1 ring-white/15">
                            <Sparkles class="w-3.5 h-3.5 text-sky-400" />
                            <span>Portal E-Buku Induk Siswa SMK</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white leading-tight">
                            Selamat Datang di Sistem Buku Induk
                        </h1>
                        <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                            Aplikasi terpadu pencatatan siklus hidup siswa, lembar hasil prestasi penilaian Semester I s.d. X, rekapitulasi absensi, dan arsip digital pasfoto.
                        </p>
                    </div>

                    <!-- Quick Action in Banner -->
                    <div class="flex items-center gap-3 shrink-0">
                        <Link
                            href="/siswa"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30 transition-all hover:scale-105 active:scale-95 cursor-pointer"
                        >
                            <Search class="w-4 h-4" />
                            <span>Buka Direktori Siswa</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 4 Modern Stat Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Siswa Aktif -->
                <div class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Siswa Aktif</span>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center ring-1 ring-blue-100 group-hover:scale-110 transition-transform">
                            <Users class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900 tracking-tight">{{ stats.totalAktif }}</span>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                            Aktif Belajar
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2 font-medium">Terdaftar di rombel kelas berjalan</p>
                </div>

                <!-- Total Jurusan -->
                <div class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Konsentrasi Keahlian</span>
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center ring-1 ring-sky-100 group-hover:scale-110 transition-transform">
                            <Layers class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900 tracking-tight">{{ jurusans.length }}</span>
                        <span class="text-[10px] font-bold text-sky-700 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-full">
                            Jurusan SMK
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2 font-medium">Program keahlian terdaftar</p>
                </div>

                <!-- Siswa Tamat / Lulus -->
                <div class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Siswa Tamat / Lulus</span>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center ring-1 ring-emerald-100 group-hover:scale-110 transition-transform">
                            <GraduationCap class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900 tracking-tight">{{ stats.totalTamat }}</span>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                            Alumni
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2 font-medium">Telah menyelesaikan studi & STTB</p>
                </div>

                <!-- Siswa Pindah / Keluar -->
                <div class="group bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Siswa Keluar / Mutasi</span>
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center ring-1 ring-rose-100 group-hover:scale-110 transition-transform">
                            <UserMinus class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900 tracking-tight">{{ stats.totalKeluar }}</span>
                        <span class="text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">
                            Mutasi
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2 font-medium">Meninggalkan sekolah sebelum tamat</p>
                </div>
            </div>

            <!-- Quick Action Cards (Gaya Modern Vue Template) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            Pintasan Operasional Cepat
                        </h2>
                        <p class="text-xs text-slate-500">Akses langsung ke alur kerja utama aplikasi</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <Link
                        href="/import"
                        class="group p-4 rounded-2xl border border-slate-200/90 hover:border-blue-400 bg-slate-50/50 hover:bg-blue-50/50 transition-all hover:-translate-y-0.5 shadow-2xs hover:shadow-xs flex items-start gap-4"
                    >
                        <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform shadow-md shadow-blue-600/20">
                            <FileSpreadsheet class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-900 group-hover:text-blue-900 flex items-center gap-1">
                                Import Identitas (Excel)
                                <ArrowUpRight class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600" />
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-snug">
                                Batch upsert ratusan siswa baru dengan format baku Excel
                            </p>
                        </div>
                    </Link>

                    <Link
                        href="/import"
                        class="group p-4 rounded-2xl border border-slate-200/90 hover:border-indigo-400 bg-slate-50/50 hover:bg-indigo-50/50 transition-all hover:-translate-y-0.5 shadow-2xs hover:shadow-xs flex items-start gap-4"
                    >
                        <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform shadow-md shadow-indigo-600/20">
                            <BookOpen class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-900 group-hover:text-indigo-900 flex items-center gap-1">
                                Import Nilai Semester
                                <ArrowUpRight class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600" />
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-snug">
                                Input rekap nilai akademik, ekskul & absensi otomatis
                            </p>
                        </div>
                    </Link>

                    <Link
                        href="/siswa"
                        class="group p-4 rounded-2xl border border-slate-200/90 hover:border-blue-400 bg-slate-50/50 hover:bg-blue-50/50 transition-all hover:-translate-y-0.5 shadow-2xs hover:shadow-xs flex items-start gap-4"
                    >
                        <div class="w-11 h-11 rounded-xl bg-[#0B1736] text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform shadow-md shadow-slate-900/20">
                            <Search class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-900 group-hover:text-blue-900 flex items-center gap-1">
                                Lembar Buku Induk
                                <ArrowUpRight class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600" />
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-snug">
                                Tampilkan lembar 2 panel dan cetak format F4/A3
                            </p>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Two Columns Section: Jurusan Stats & Recent Students -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Jurusan Card (1 col) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-xs font-black text-slate-900 uppercase tracking-wide">
                                Persebaran Jurusan
                            </h2>
                            <p class="text-[11px] text-slate-500">Siswa aktif per konsentrasi</p>
                        </div>
                        <span class="text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full">
                            {{ jurusans.length }} Jurusan
                        </span>
                    </div>

                    <div class="mt-4 space-y-2.5 flex-1">
                        <div
                            v-for="jurusan in jurusans"
                            :key="jurusan.id"
                            class="p-3 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-sky-50/50 transition-colors flex items-center justify-between"
                        >
                            <div class="min-w-0 pr-2">
                                <div class="font-bold text-xs text-slate-900 truncate">
                                    {{ jurusan.nama }}
                                </div>
                                <div class="text-[11px] text-slate-500 font-semibold mt-0.5">
                                    Kode: <span class="text-blue-600 font-mono">{{ jurusan.kode }}</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-base font-black text-blue-900">{{ jurusan.total }}</span>
                                <span class="text-[10px] text-slate-500 block leading-none font-medium">siswa</span>
                            </div>
                        </div>

                        <div v-if="jurusans.length === 0" class="text-center py-8 text-xs text-slate-400">
                            Belum ada konsentrasi keahlian.
                        </div>
                    </div>
                </div>

                <!-- Recent Students (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-xs font-black text-slate-900 uppercase tracking-wide">
                                Siswa Diperbarui Terkini
                            </h2>
                            <p class="text-[11px] text-slate-500">Pembaruan biodata atau nilai terakhir</p>
                        </div>
                        <Link
                            href="/siswa"
                            class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 transition-colors"
                        >
                            <span>Lihat Direktori Lengkap</span>
                            <ChevronRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>

                    <div class="mt-4 overflow-x-auto flex-1">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-[#0B1736] text-white uppercase text-[10px] font-bold tracking-wider">
                                <tr>
                                    <th class="px-3 py-2.5 rounded-l-lg">NIS / NISN</th>
                                    <th class="px-3 py-2.5">Nama Siswa</th>
                                    <th class="px-3 py-2.5">Kelas / Jurusan</th>
                                    <th class="px-3 py-2.5 text-center">Status</th>
                                    <th class="px-3 py-2.5 text-right rounded-r-lg">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-sans">
                                <tr
                                    v-for="s in recentSiswa"
                                    :key="s.id"
                                    class="hover:bg-blue-50/50 transition-colors"
                                >
                                    <td class="px-3 py-2.5 font-mono text-[11px] font-semibold text-slate-700 whitespace-nowrap">
                                        {{ s.nis }} / {{ s.nisn }}
                                    </td>
                                    <td class="px-3 py-2.5 font-bold text-slate-900 whitespace-nowrap">
                                        {{ s.nama_lengkap }}
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-600 whitespace-nowrap">
                                        {{ s.kelas_saat_ini || '-' }}
                                        <span v-if="s.konsentrasi_keahlian" class="text-slate-400 font-medium">
                                            ({{ s.konsentrasi_keahlian.kode_singkat }})
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                        <span
                                            v-if="s.status_siswa === 'AKTIF'"
                                            class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"
                                        >
                                            AKTIF
                                        </span>
                                        <span
                                            v-else-if="s.status_siswa === 'TAMAT'"
                                            class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200"
                                        >
                                            TAMAT
                                        </span>
                                        <span
                                            v-else
                                            class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200"
                                        >
                                            KELUAR
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                        <Link
                                            :href="`/siswa/${s.id}/buku-induk`"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] shadow-2xs transition-all active:scale-95 cursor-pointer"
                                        >
                                            <BookOpen class="w-3 h-3" />
                                            <span>Buka Buku Induk</span>
                                        </Link>
                                    </td>
                                </tr>

                                <tr v-if="recentSiswa.length === 0">
                                    <td colspan="5" class="text-center py-10 text-slate-400">
                                        Belum ada data siswa.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</template>
