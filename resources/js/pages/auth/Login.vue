<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, EyeOff } from '@lucide/vue';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();

const showPassword = ref(false);

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

const fillDemo = (role: 'admin' | 'tu' = 'admin') => {
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
    <Head title="Login - SI KARANJI" />

    <!-- Container Penuh Layar: 50/50 Split Edge-to-Edge Responsif -->
    <div class="min-h-screen w-full flex flex-col md:flex-row bg-white font-sans antialiased selection:bg-[#EAF4FC] selection:text-[#2B7CC2] overflow-x-hidden">
        
        <!-- ================= SISI KIRI (50% Lebar): Visual Art Hero Maritim SI KARANJI ================= -->
        <div class="w-full md:w-1/2 min-h-[460px] md:min-h-screen relative flex flex-col justify-between p-8 sm:p-12 lg:p-16 overflow-hidden select-none bg-gradient-to-br from-[#0B2545] via-[#123E6E] to-[#1F6BAF] text-white">
            
            <!-- SVG Fluid Organic Wave Layers (Siluet Cair Maritim Gorontalo dengan Kedalaman Berlapis) -->
            <svg
                class="absolute inset-0 w-full h-full object-cover pointer-events-none"
                viewBox="0 0 600 800"
                preserveAspectRatio="none"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <defs>
                    <!-- Gradasi Ombak Cyan Terang / Transparan -->
                    <linearGradient id="cyanWave" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#5DAEF5" stop-opacity="0.3" />
                        <stop offset="60%" stop-color="#38BDF8" stop-opacity="0.15" />
                        <stop offset="100%" stop-color="#BFE3FA" stop-opacity="0.05" />
                    </linearGradient>

                    <!-- Gradasi Ombak Biru Maritim Utama -->
                    <linearGradient id="oceanWaveMain" x1="20%" y1="0%" x2="80%" y2="100%">
                        <stop offset="0%" stop-color="#2B7CC2" stop-opacity="0.95" />
                        <stop offset="50%" stop-color="#1B5B92" stop-opacity="0.98" />
                        <stop offset="100%" stop-color="#0F3863" stop-opacity="0.95" />
                    </linearGradient>

                    <!-- Ombak Kedalaman Bawah -->
                    <linearGradient id="deepWave" x1="0%" y1="100%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#081A32" stop-opacity="0.85" />
                        <stop offset="100%" stop-color="#0B2545" stop-opacity="0.5" />
                    </linearGradient>
                </defs>

                <!-- Lapisan Ombak 1: Gelombang Halus Cyan di Atas & Kanan -->
                <path
                    d="M 160,0
                       C 280,120 420,80 520,240
                       C 580,330 600,420 600,560
                       L 600,0
                       Z"
                    fill="url(#cyanWave)"
                />

                <!-- Lapisan Ombak 2: Siluet Utama Gelombang Fluida Organik (Mengalir anggun di belakang konten) -->
                <path
                    d="M 0,220
                       C 120,180 200,340 340,300
                       C 480,260 520,120 600,80
                       L 600,800
                       L 0,800
                       Z"
                    fill="url(#oceanWaveMain)"
                />

                <!-- Lapisan Ombak 3: Kurva Organik Bawah Sisi Kiri -->
                <path
                    d="M 0,480
                       C 140,420 260,620 420,540
                       C 520,490 560,580 600,560
                       L 600,800
                       L 0,800
                       Z"
                    fill="url(#deepWave)"
                />

                <!-- Lapisan Ombak 4: Aksen Lengkungan Halus Kiri Atas (Mirip Bentuk Lobe Contoh) -->
                <path
                    d="M 0,0
                       L 260,0
                       C 240,90 180,160 120,180
                       C 60,200 0,160 0,120
                       Z"
                    fill="url(#cyanWave)"
                />
            </svg>

            <!-- Ambient Glow Lembut di Sekitar Maskot -->
            <div class="pointer-events-none absolute top-1/2 right-12 -translate-y-1/2 w-80 h-80 rounded-full bg-[#38BDF8]/20 blur-3xl"></div>

            <!-- ================= HEADER KIRI ATAS: Logo Resmi & Nama SMK Negeri 1 Gorontalo ================= -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-10 h-11 flex items-center justify-center shrink-0">
                    <img
                        src="/images/logo-smkn1-gorontalo.webp"
                        alt="Logo SMK Negeri 1 Gorontalo"
                        class="w-full h-full object-contain drop-shadow-md"
                    />
                </div>
                <span class="text-sm sm:text-base font-extrabold tracking-wide text-white drop-shadow-xs">
                    SMK Negeri 1 Gorontalo
                </span>
            </div>

            <!-- ================= BAGIAN TENGAH: Teks Sambutan & Maskot Besar ================= -->
            <div class="relative z-10 my-auto py-10 lg:py-14">
                <div class="flex flex-col lg:flex-row items-center lg:items-center justify-between gap-8 lg:gap-10">
                    
                    <!-- Kolom Teks: Welcome Back Bersih & Berbobot -->
                    <div class="space-y-3.5 text-center lg:text-left">
                        <span class="inline-block text-xs font-bold tracking-widest text-[#BFE3FA] uppercase">
                            Sistem Informasi Buku Induk
                        </span>

                        <h1 class="text-5xl sm:text-6xl lg:text-[64px] xl:text-[72px] font-black text-white tracking-tight leading-[0.92] drop-shadow-md">
                            Welcome<br />Back!
                        </h1>

                        <p class="text-xs sm:text-sm text-blue-100/90 font-medium leading-relaxed max-w-[290px] pt-1">
                            Kelola Arsip, Rekap Administrasi, Nilai, dan Jejak Induk. SMK Negeri 1 Gorontalo
                        </p>
                    </div>

                    <!-- Kolom Maskot: Hiu Paus SI KARANJI Ukuran Besar & Menawan -->
                    <div class="shrink-0 relative group">
                        <!-- Glow Radial di Belakang Maskot -->
                        <div class="absolute inset-0 bg-[#5DAEF5]/25 rounded-full blur-2xl transform scale-95 pointer-events-none group-hover:scale-110 transition-transform duration-500"></div>

                        <img
                            src="/images/sikaranji-mascot.webp"
                            alt="Maskot SI KARANJI"
                            class="w-52 h-56 sm:w-64 sm:h-68 lg:w-72 lg:h-76 xl:w-80 xl:h-84 object-contain drop-shadow-[0_20px_35px_rgba(0,0,0,0.38)] hover:scale-105 hover:-translate-y-1 transition-all duration-300 relative z-10"
                        />
                    </div>

                </div>
            </div>

            <!-- ================= FOOTER BAWAH: Tagline Filosofis Khas Gorontalo Terintegrasi ================= -->
            <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between gap-2 pt-4 border-t border-white/15 text-xs text-blue-200/80">
                <span class="font-medium">
                    &copy; {{ new Date().getFullYear() }} SMK Negeri 1 Gorontalo
                </span>
                <span class="italic font-serif tracking-wide text-blue-100/90">
                    &ldquo;Menganyam data induk, menjaga jejak siswa.&rdquo;
                </span>
            </div>

        </div>

        <!-- ================= SISI KANAN (50% Lebar): Form Login Bersih dalam Kartu Berbingkai Elegan ================= -->
        <div class="w-full md:w-1/2 min-h-screen flex items-center justify-center p-6 sm:p-10 lg:p-16 bg-[#F8FAFC]">
            
            <!-- Kotak Form Berbingkai Halus & Bersih (Clean Card Container) -->
            <div class="w-full max-w-[430px] mx-auto bg-white rounded-2xl border border-[#E2E8F0] shadow-xl shadow-slate-200/50 p-8 sm:p-10 space-y-6">
                
                <!-- Judul & Subjudul Form -->
                <div class="space-y-1.5 text-left">
                    <h2 class="text-3xl sm:text-[34px] font-extrabold text-[#0F3A5F] tracking-tight">
                        Login
                    </h2>
                    <p class="text-xs sm:text-sm text-[#64748B] font-normal leading-relaxed">
                        Welcome back! Please login to your account.
                    </p>
                </div>

                <!-- Alert Status Fortify (Jika ada pesan) -->
                <div
                    v-if="status"
                    class="p-3 text-center text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200"
                >
                    {{ status }}
                </div>

                <!-- Form Login -->
                <form @submit.prevent="submit" class="space-y-4">
                    
                    <!-- Field 1: User Name -->
                    <div class="space-y-1.5 text-left">
                        <label for="email" class="block text-xs sm:text-sm text-[#64748B] font-medium">
                            User Name
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="username@gmail.com"
                            :class="[
                                'w-full px-4 py-3 rounded-xl border text-sm text-[#0F3A5F] placeholder-[#94A3B8]/60 bg-[#F8FAFC]/60 focus:bg-white transition-all outline-none',
                                form.errors.email
                                    ? 'border-rose-500 ring-1 ring-rose-500'
                                    : 'border-[#E2E8F0] focus:border-[#2B7CC2] focus:ring-4 focus:ring-[#2B7CC2]/10'
                            ]"
                        />
                        <p v-if="form.errors.email" class="text-xs text-rose-500 pt-0.5">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Field 2: Password (dengan tombol toggle show/hide) -->
                    <div class="space-y-1.5 text-left">
                        <label for="password" class="block text-xs sm:text-sm text-[#64748B] font-medium">
                            Password
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••••"
                                :class="[
                                    'w-full pl-4 pr-11 py-3 rounded-xl border text-sm text-[#0F3A5F] placeholder-[#94A3B8]/60 bg-[#F8FAFC]/60 focus:bg-white transition-all outline-none',
                                    form.errors.password
                                        ? 'border-rose-500 ring-1 ring-rose-500'
                                        : 'border-[#E2E8F0] focus:border-[#2B7CC2] focus:ring-4 focus:ring-[#2B7CC2]/10'
                                ]"
                            />
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#2B7CC2] focus:outline-none transition-colors p-1.5 rounded-md cursor-pointer"
                                :title="showPassword ? 'Sembunyikan password' : 'Lihat password'"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-xs text-rose-500 pt-0.5">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Baris Remember Me & Forget Password -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none text-[#64748B]">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 rounded border-[#CBD5E1] text-[#2B7CC2] focus:ring-[#2B7CC2] cursor-pointer transition-colors"
                            />
                            <span>Remember Me</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            href="/forgot-password"
                            class="text-[#64748B] hover:text-[#2B7CC2] transition-colors"
                        >
                            Forget Password?
                        </Link>
                    </div>

                    <!-- Tombol Login (Solid Primary Blue) -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            :class="[
                                'w-full py-3.5 rounded-xl text-sm font-bold text-white bg-[#2B7CC2] hover:bg-[#226AA8] active:bg-[#165A94] transition-all shadow-sm hover:shadow cursor-pointer',
                                form.processing ? 'opacity-80 cursor-wait' : ''
                            ]"
                        >
                            <span v-if="!form.processing">Login</span>
                            <span v-else class="inline-flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span>Memproses...</span>
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Navigasi Kembali ke Beranda (Persis sesuai referensi di bawah form) -->
                <div class="text-center text-xs sm:text-sm text-[#64748B] pt-2 border-t border-[#F1F5F9]">
                    <span>Kembali ke halaman </span>
                    <Link
                        href="/"
                        class="font-semibold text-[#2B7CC2] hover:text-[#226AA8] underline underline-offset-2 transition-colors cursor-pointer"
                    >
                        Beranda
                    </Link>
                </div>

            </div>
        </div>

    </div>
</template>
