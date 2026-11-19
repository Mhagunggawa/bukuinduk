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

    <div class="mx-auto max-w-7xl space-y-6">
        <!-- Modern Hero Banner (Gaya Modern Vue Template) -->
        <div
            class="relative overflow-hidden rounded-3xl border border-slate-800/40 bg-gradient-to-r from-[#0B1736] via-blue-900 to-indigo-950 p-6 text-white shadow-xl sm:p-8"
        >
            <!-- Decorative Blur Circles -->
            <div
                class="pointer-events-none absolute -top-16 -right-16 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"
            ></div>
            <div
                class="pointer-events-none absolute right-32 -bottom-16 h-60 w-60 rounded-full bg-sky-400/15 blur-3xl"
            ></div>

            <div
                class="relative z-10 flex flex-col justify-between gap-6 lg:flex-row lg:items-center"
            >
                <div class="max-w-2xl space-y-2">
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-sky-300 ring-1 ring-white/15 backdrop-blur-md"
                    >
                        <Sparkles class="h-3.5 w-3.5 text-sky-400" />
                        <span>Portal E-Buku Induk Siswa SMK</span>
                    </div>
                    <h1
                        class="text-2xl leading-tight font-black tracking-tight text-white sm:text-3xl"
                    >
                        Selamat Datang di Sistem Buku Induk
                    </h1>
                    <p
                        class="text-xs leading-relaxed text-slate-300 sm:text-sm"
                    >
                        Aplikasi terpadu pencatatan siklus hidup siswa, lembar
                        hasil prestasi penilaian Semester I s.d. X, rekapitulasi
                        absensi, dan arsip digital pasfoto.
                    </p>
                </div>

                <!-- Quick Action in Banner -->
                <div class="flex shrink-0 items-center gap-3">
                    <Link
                        href="/siswa"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-xs font-bold text-white shadow-lg shadow-blue-600/30 transition-all hover:scale-105 hover:bg-blue-500 active:scale-95"
                    >
                        <Search class="h-4 w-4" />
                        <span>Buka Direktori Siswa</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- 4 Modern Stat Metric Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Siswa Aktif -->
            <div
                class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:-translate-y-0.5 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-[11px] font-bold tracking-wider text-slate-500 uppercase"
                        >Siswa Aktif</span
                    >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 transition-transform group-hover:scale-110"
                    >
                        <Users class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span
                        class="text-3xl font-black tracking-tight text-slate-900"
                        >{{ stats.totalAktif }}</span
                    >
                    <span
                        class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700"
                    >
                        Aktif Belajar
                    </span>
                </div>
                <p class="mt-2 text-[11px] font-medium text-slate-500">
                    Terdaftar di rombel kelas berjalan
                </p>
            </div>

            <!-- Total Jurusan -->
            <div
                class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:-translate-y-0.5 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-[11px] font-bold tracking-wider text-slate-500 uppercase"
                        >Konsentrasi Keahlian</span
                    >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-100 transition-transform group-hover:scale-110"
                    >
                        <Layers class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span
                        class="text-3xl font-black tracking-tight text-slate-900"
                        >{{ jurusans.length }}</span
                    >
                    <span
                        class="rounded-full border border-sky-200 bg-sky-50 px-2 py-0.5 text-[10px] font-bold text-sky-700"
                    >
                        Jurusan SMK
                    </span>
                </div>
                <p class="mt-2 text-[11px] font-medium text-slate-500">
                    Program keahlian terdaftar
                </p>
            </div>

            <!-- Siswa Tamat / Lulus -->
            <div
                class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:-translate-y-0.5 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-[11px] font-bold tracking-wider text-slate-500 uppercase"
                        >Siswa Tamat / Lulus</span
                    >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 transition-transform group-hover:scale-110"
                    >
                        <GraduationCap class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span
                        class="text-3xl font-black tracking-tight text-slate-900"
                        >{{ stats.totalTamat }}</span
                    >
                    <span
                        class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700"
                    >
                        Alumni
                    </span>
                </div>
                <p class="mt-2 text-[11px] font-medium text-slate-500">
                    Telah menyelesaikan studi & STTB
                </p>
            </div>

            <!-- Siswa Pindah / Keluar -->
            <div
                class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:-translate-y-0.5 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-[11px] font-bold tracking-wider text-slate-500 uppercase"
                        >Siswa Keluar / Mutasi</span
                    >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 ring-1 ring-rose-100 transition-transform group-hover:scale-110"
                    >
                        <UserMinus class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span
                        class="text-3xl font-black tracking-tight text-slate-900"
                        >{{ stats.totalKeluar }}</span
                    >
                    <span
                        class="rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-700"
                    >
                        Mutasi
                    </span>
                </div>
                <p class="mt-2 text-[11px] font-medium text-slate-500">
                    Meninggalkan sekolah sebelum tamat
                </p>
            </div>
        </div>

        <!-- Quick Action Cards (Gaya Modern Vue Template) -->
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs"
        >
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2
                        class="flex items-center gap-2 text-sm font-black tracking-wide text-slate-900 uppercase"
                    >
                        <span
                            class="h-2.5 w-2.5 rounded-full bg-blue-600"
                        ></span>
                        Pintasan Operasional Cepat
                    </h2>
                    <p class="text-xs text-slate-500">
                        Akses langsung ke alur kerja utama aplikasi
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Link
                    href="/import"
                    class="group flex items-start gap-4 rounded-2xl border border-slate-200/90 bg-slate-50/50 p-4 shadow-2xs transition-all hover:-translate-y-0.5 hover:border-blue-400 hover:bg-blue-50/50 hover:shadow-xs"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-600/20 transition-transform group-hover:scale-105"
                    >
                        <FileSpreadsheet class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <div
                            class="flex items-center gap-1 text-xs font-bold text-slate-900 group-hover:text-blue-900"
                        >
                            Import Identitas (Excel)
                            <ArrowUpRight
                                class="h-3.5 w-3.5 text-slate-400 group-hover:text-blue-600"
                            />
                        </div>
                        <p class="mt-1 text-[11px] leading-snug text-slate-500">
                            Batch upsert ratusan siswa baru dengan format baku
                            Excel
                        </p>
                    </div>
                </Link>

                <Link
                    href="/import"
                    class="group flex items-start gap-4 rounded-2xl border border-slate-200/90 bg-slate-50/50 p-4 shadow-2xs transition-all hover:-translate-y-0.5 hover:border-indigo-400 hover:bg-indigo-50/50 hover:shadow-xs"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/20 transition-transform group-hover:scale-105"
                    >
                        <BookOpen class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <div
                            class="flex items-center gap-1 text-xs font-bold text-slate-900 group-hover:text-indigo-900"
                        >
                            Import Nilai Semester
                            <ArrowUpRight
                                class="h-3.5 w-3.5 text-slate-400 group-hover:text-indigo-600"
                            />
                        </div>
                        <p class="mt-1 text-[11px] leading-snug text-slate-500">
                            Input rekap nilai akademik, ekskul & absensi
                            otomatis
                        </p>
                    </div>
                </Link>

                <Link
                    href="/siswa"
                    class="group flex items-start gap-4 rounded-2xl border border-slate-200/90 bg-slate-50/50 p-4 shadow-2xs transition-all hover:-translate-y-0.5 hover:border-blue-400 hover:bg-blue-50/50 hover:shadow-xs"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#0B1736] text-white shadow-md shadow-slate-900/20 transition-transform group-hover:scale-105"
                    >
                        <Search class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <div
                            class="flex items-center gap-1 text-xs font-bold text-slate-900 group-hover:text-blue-900"
                        >
                            Lembar Buku Induk
                            <ArrowUpRight
                                class="h-3.5 w-3.5 text-slate-400 group-hover:text-blue-600"
                            />
                        </div>
                        <p class="mt-1 text-[11px] leading-snug text-slate-500">
                            Tampilkan lembar 2 panel dan cetak format F4/A3
                        </p>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Two Columns Section: Jurusan Stats & Recent Students -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Jurusan Card (1 col) -->
            <div
                class="flex flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3"
                >
                    <div>
                        <h2
                            class="text-xs font-black tracking-wide text-slate-900 uppercase"
                        >
                            Persebaran Jurusan
                        </h2>
                        <p class="text-[11px] text-slate-500">
                            Siswa aktif per konsentrasi
                        </p>
                    </div>
                    <span
                        class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-[11px] font-bold text-blue-700"
                    >
                        {{ jurusans.length }} Jurusan
                    </span>
                </div>

                <div class="mt-4 flex-1 space-y-2.5">
                    <div
                        v-for="jurusan in jurusans"
                        :key="jurusan.id"
                        class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/60 p-3 transition-colors hover:bg-sky-50/50"
                    >
                        <div class="min-w-0 pr-2">
                            <div
                                class="truncate text-xs font-bold text-slate-900"
                            >
                                {{ jurusan.nama }}
                            </div>
                            <div
                                class="mt-0.5 text-[11px] font-semibold text-slate-500"
                            >
                                Kode:
                                <span class="font-mono text-blue-600">{{
                                    jurusan.kode
                                }}</span>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="text-base font-black text-blue-900">{{
                                jurusan.total
                            }}</span>
                            <span
                                class="block text-[10px] leading-none font-medium text-slate-500"
                                >siswa</span
                            >
                        </div>
                    </div>

                    <div
                        v-if="jurusans.length === 0"
                        class="py-8 text-center text-xs text-slate-400"
                    >
                        Belum ada konsentrasi keahlian.
                    </div>
                </div>
            </div>

            <!-- Recent Students (2 cols) -->
            <div
                class="flex flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs lg:col-span-2"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3"
                >
                    <div>
                        <h2
                            class="text-xs font-black tracking-wide text-slate-900 uppercase"
                        >
                            Siswa Diperbarui Terkini
                        </h2>
                        <p class="text-[11px] text-slate-500">
                            Pembaruan biodata atau nilai terakhir
                        </p>
                    </div>
                    <Link
                        href="/siswa"
                        class="flex items-center gap-1 text-xs font-bold text-blue-600 transition-colors hover:text-blue-800"
                    >
                        <span>Lihat Direktori Lengkap</span>
                        <ChevronRight class="h-3.5 w-3.5" />
                    </Link>
                </div>

                <div class="mt-4 flex-1 overflow-x-auto">
                    <table class="w-full border-collapse text-left text-xs">
                        <thead
                            class="bg-[#0B1736] text-[10px] font-bold tracking-wider text-white uppercase"
                        >
                            <tr>
                                <th class="rounded-l-lg px-3 py-2.5">
                                    NIS / NISN
                                </th>
                                <th class="px-3 py-2.5">Nama Siswa</th>
                                <th class="px-3 py-2.5">Kelas / Jurusan</th>
                                <th class="px-3 py-2.5 text-center">Status</th>
                                <th class="rounded-r-lg px-3 py-2.5 text-right">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-sans">
                            <tr
                                v-for="s in recentSiswa"
                                :key="s.id"
                                class="transition-colors hover:bg-blue-50/50"
                            >
                                <td
                                    class="px-3 py-2.5 font-mono text-[11px] font-semibold whitespace-nowrap text-slate-700"
                                >
                                    {{ s.nis }} / {{ s.nisn }}
                                </td>
                                <td
                                    class="px-3 py-2.5 font-bold whitespace-nowrap text-slate-900"
                                >
                                    {{ s.nama_lengkap }}
                                </td>
                                <td
                                    class="px-3 py-2.5 whitespace-nowrap text-slate-600"
                                >
                                    {{ s.kelas_saat_ini || '-' }}
                                    <span
                                        v-if="s.konsentrasi_keahlian"
                                        class="font-medium text-slate-400"
                                    >
                                        ({{
                                            s.konsentrasi_keahlian.kode_singkat
                                        }})
                                    </span>
                                </td>
                                <td
                                    class="px-3 py-2.5 text-center whitespace-nowrap"
                                >
                                    <span
                                        v-if="s.status_siswa === 'AKTIF'"
                                        class="inline-block rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800"
                                    >
                                        AKTIF
                                    </span>
                                    <span
                                        v-else-if="s.status_siswa === 'TAMAT'"
                                        class="inline-block rounded-full border border-blue-200 bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold text-blue-800"
                                    >
                                        TAMAT
                                    </span>
                                    <span
                                        v-else
                                        class="inline-block rounded-full border border-rose-200 bg-rose-100 px-2.5 py-0.5 text-[10px] font-bold text-rose-800"
                                    >
                                        KELUAR
                                    </span>
                                </td>
                                <td
                                    class="px-3 py-2.5 text-right whitespace-nowrap"
                                >
                                    <Link
                                        :href="`/siswa/${s.id}/buku-induk`"
                                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1 text-[11px] font-bold text-white shadow-2xs transition-all hover:bg-blue-700 active:scale-95"
                                    >
                                        <BookOpen class="h-3 w-3" />
                                        <span>Buka Buku Induk</span>
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="recentSiswa.length === 0">
                                <td
                                    colspan="5"
                                    class="py-10 text-center text-slate-400"
                                >
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
