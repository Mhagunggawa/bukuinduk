<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import AppSidebar from '@/components/AppSidebar.vue';
import LogoutModal from '@/components/LogoutModal.vue';
import AppToast from '@/components/base/AppToast.vue';
import { useAppearance } from '@/composables/useAppearance';
import { useToast } from '@/composables/useToast';
import {
    Menu,
    ChevronRight,
    ChevronDown,
    Calendar,
    User,
    Shield,
    LogOut,
    PanelLeftClose,
    PanelLeftOpen,
    Sun,
    Moon,
} from '@lucide/vue';

interface Breadcrumb {
    title: string;
    href?: string;
}

const props = withDefaults(
    defineProps<{
        breadcrumbs?: Breadcrumb[];
    }>(),
    {
        breadcrumbs: () => [],
    }
);

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const userInitials = computed(() => {
    const name = user.value?.name || 'User';
    return name
        .split(' ')
        .map((n: string) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const userRoleLabel = computed(() => {
    if (!user.value) return 'Pengguna';
    if (user.value.role === 'admin') return 'Administrator';
    if (user.value.role === 'tata_usaha' || user.value.role === 'tata usaha') return 'Tata Usaha';
    return user.value.role || 'Staf Sekolah';
});

const isAdmin = computed(() => ['admin', 'super admin'].includes(user.value?.role));

const { appearance, resolvedAppearance, updateAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === 'dark');

const toggleDarkMode = () => {
    updateAppearance(isDark.value ? 'light' : 'dark');
};

const isUserMenuOpen = ref(false);
const isLogoutModalOpen = ref(false);
const userMenuRef = ref<HTMLElement | null>(null);

onClickOutside(userMenuRef, () => {
    isUserMenuOpen.value = false;
});

const openLogoutModal = () => {
    isUserMenuOpen.value = false;
    isLogoutModalOpen.value = true;
};

// Sistem Notifikasi Toast Bersih & Auto-Dismiss
const { toastState, show: showToast, close: closeToast } = useToast();

watch(
    () => (page.props as any).flash,
    (newFlash) => {
        if (!newFlash) return;
        if (newFlash.success) {
            showToast({
                type: 'success',
                title: 'Berhasil',
                message: newFlash.success,
                duration: 4500,
            });
        } else if (newFlash.error) {
            showToast({
                type: 'error',
                title: 'Pemberitahuan Sistem',
                message: newFlash.error,
                duration: 5500,
            });
        } else if (newFlash.warning) {
            showToast({
                type: 'warning',
                title: 'Peringatan',
                message: newFlash.warning,
                duration: 5000,
            });
        } else if (newFlash.info) {
            showToast({
                type: 'info',
                title: 'Informasi',
                message: newFlash.info,
                duration: 4500,
            });
        }
    },
    { deep: true, immediate: true }
);

const isSidebarOpen = ref(false);
const isSidebarCollapsed = ref(false);

onMounted(() => {
    const saved = localStorage.getItem('si-karanji-sidebar-collapsed');
    if (saved !== null) {
        isSidebarCollapsed.value = saved === 'true';
    }
});

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebar = () => {
    isSidebarOpen.value = false;
};

const toggleCollapse = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    localStorage.setItem('si-karanji-sidebar-collapsed', String(isSidebarCollapsed.value));
};

const currentUrl = computed(() => page.url);

const resolvedRoute = computed(() => {
    const url = currentUrl.value;

    if (url === '/dashboard' || url === '/') {
        const title = userRoleLabel.value ? `Dashboard ${userRoleLabel.value}` : 'Dashboard';
        return {
            pageTitle: title,
            breadcrumbs: [{ title, href: '/dashboard' }],
        };
    }
    if (url.startsWith('/siswa') && url.includes('/buku-induk')) {
        return {
            pageTitle: 'Buku Induk Siswa',
            breadcrumbs: [
                { title: 'Direktori Siswa', href: '/siswa' },
                { title: 'Buku Induk Siswa' },
            ],
        };
    }
    if (url.startsWith('/siswa')) {
        return {
            pageTitle: 'Direktori Siswa',
            breadcrumbs: [{ title: 'Direktori Siswa', href: '/siswa' }],
        };
    }
    if (url.startsWith('/import')) {
        return {
            pageTitle: 'Import Data Massal',
            breadcrumbs: [{ title: 'Import Data Massal', href: '/import' }],
        };
    }
    if (url.startsWith('/master-data')) {
        return {
            pageTitle: 'Data Master',
            breadcrumbs: [{ title: 'Data Master', href: '/master-data' }],
        };
    }
    if (url.startsWith('/users')) {
        return {
            pageTitle: 'Manajemen Pengguna',
            breadcrumbs: [{ title: 'Manajemen Pengguna', href: '/users' }],
        };
    }
    if (url.startsWith('/settings/profile')) {
        return {
            pageTitle: 'Profil Pengguna',
            breadcrumbs: [
                { title: 'Profil Pengguna', href: '/settings/profile' },
            ],
        };
    }
    if (url.startsWith('/settings/security')) {
        return {
            pageTitle: 'Keamanan & Sandi',
            breadcrumbs: [
                { title: 'Keamanan & Sandi', href: '/settings/security' },
            ],
        };
    }

    return {
        pageTitle: 'Halaman',
        breadcrumbs: [],
    };
});

const activeBreadcrumbs = computed<Breadcrumb[]>(() => {
    if (props.breadcrumbs && props.breadcrumbs.length > 0) {
        return props.breadcrumbs;
    }
    return resolvedRoute.value.breadcrumbs;
});

const activePageTitle = computed(() => {
    if (activeBreadcrumbs.value.length > 0) {
        return activeBreadcrumbs.value[activeBreadcrumbs.value.length - 1].title;
    }
    return resolvedRoute.value.pageTitle;
});
</script>

<template>
    <div class="fixed inset-0 overflow-hidden print:static print:inset-auto print:overflow-visible print:bg-white bg-[#F8FAFC] font-sans text-[#0F3A5F] flex flex-col md:flex-row antialiased">
        <!-- AppSidebar Component -->
        <AppSidebar
            :open="isSidebarOpen"
            :collapsed="isSidebarCollapsed"
            @close="closeSidebar"
            @toggle-collapse="toggleCollapse"
        />

        <!-- Right Main Viewport -->
        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden print:h-auto print:overflow-visible bg-[#F8FAFC] print:bg-white">
            <!-- Topbar / Navbar (Terkunci di Posisi Atas, Disembunyikan saat Print) -->
            <header class="h-16 bg-white border-b border-[#E2E8F0] px-4 sm:px-6 flex items-center justify-between shrink-0 z-30 print:hidden">
                <div class="flex items-center gap-3.5 min-w-0">
                    <!-- Tombol Buka Menu (Mobile) -->
                    <button
                        type="button"
                        class="md:hidden p-1.5 text-slate-500 hover:text-[#0F3A5F] rounded-[8px] hover:bg-slate-100 transition-colors cursor-pointer shrink-0"
                        title="Buka Menu"
                        aria-label="Buka Menu"
                        @click="toggleSidebar"
                    >
                        <Menu class="w-5 h-5" />
                    </button>

                    <!-- Tombol Ciutkan / Perluas Sidebar (Desktop) -->
                    <button
                        type="button"
                        class="hidden md:flex p-1.5 text-[#64748B] hover:text-[#0F3A5F] hover:bg-slate-100 rounded-[8px] transition-colors cursor-pointer shrink-0"
                        :title="isSidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
                        :aria-label="isSidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
                        @click="toggleCollapse"
                    >
                        <PanelLeftOpen v-if="isSidebarCollapsed" class="w-5 h-5" />
                        <PanelLeftClose v-else class="w-5 h-5" />
                    </button>

                    <!-- Pemisah Vertikal Halus (Desktop) -->
                    <div class="hidden md:block w-px h-5 bg-[#E2E8F0] shrink-0" />

                    <!-- Single-line Clean Header Breadcrumbs: "SI KARANJI > Dashboard Administrator" -->
                    <nav class="flex items-center min-w-0 text-sm sm:text-[15px]">
                        <Link
                            href="/dashboard"
                            class="text-slate-400 hover:text-[#2B7CC2] transition-colors shrink-0 font-medium tracking-tight"
                        >
                            SI KARANJI
                        </Link>

                        <template v-for="(crumb, idx) in activeBreadcrumbs" :key="idx">
                            <ChevronRight class="w-4 h-4 mx-1.5 sm:mx-2 text-slate-400 shrink-0" />
                            <Link
                                v-if="crumb.href && idx < activeBreadcrumbs.length - 1"
                                :href="crumb.href"
                                class="text-slate-400 hover:text-[#2B7CC2] transition-colors truncate max-w-[120px] sm:max-w-none font-medium tracking-tight"
                            >
                                {{ crumb.title }}
                            </Link>
                            <span
                                v-else
                                class="text-[#0F3A5F] font-bold tracking-tight truncate"
                            >
                                {{ crumb.title }}
                            </span>
                        </template>
                    </nav>
                </div>

                <!-- Right Topbar Controls (Termasuk Profil Pengguna, Dark Mode & Logout) -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- Tombol Dark Mode / Light Mode Toggle (Header Kanan Atas) -->
                    <button
                        type="button"
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white dark:bg-[#1E293B] border border-[#E2E8F0] dark:border-[#223354] text-[#64748B] dark:text-[#F1F5F9] hover:text-[#0F3A5F] dark:hover:text-[#38BDF8] hover:bg-[#F8FAFC] dark:hover:bg-[#283548] flex items-center justify-center transition-all cursor-pointer shadow-2xs group"
                        :title="isDark ? 'Ganti ke Mode Terang (Light Mode)' : 'Ganti ke Mode Gelap (Dark Mode)'"
                        :aria-label="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                        @click="toggleDarkMode"
                    >
                        <Sun v-if="isDark" class="w-4 h-4 text-amber-400 group-hover:rotate-45 transition-transform" />
                        <Moon v-else class="w-4 h-4 text-[#0F3A5F] group-hover:-rotate-12 transition-transform" />
                    </button>

                    <!-- Garis Pembatas Vertikal Antara Tombol Dark Mode dan Profil Pengguna -->
                    <div class="h-6 w-px bg-[#E2E8F0] dark:bg-[#223354] mx-0.5 sm:mx-1 shrink-0" aria-hidden="true" />

                    <!-- User Profile & Dropdown Menu (Gaya Warna Selaras SI KARANJI) -->
                    <div ref="userMenuRef" class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-2.5 sm:gap-3 p-1 sm:p-1.5 rounded-xl hover:bg-[#EAF4FC]/60 dark:hover:bg-[#1E293B]/70 transition-all cursor-pointer group text-left select-none outline-none border border-transparent hover:border-[#BFE3FA]/70 dark:hover:border-[#223354]"
                            :title="user?.name || 'User'"
                            @click="isUserMenuOpen = !isUserMenuOpen"
                        >
                            <!-- Avatar Gradien Biru-Navy Selaras SI KARANJI -->
                            <div
                                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-[#2B7CC2] via-[#246FB1] to-[#0F3A5F] text-white font-bold text-xs sm:text-sm flex items-center justify-center shrink-0 shadow-sm ring-2 ring-[#BFE3FA]/60 dark:ring-[#38BDF8]/30 tracking-tight select-none"
                            >
                                {{ userInitials }}
                            </div>

                            <!-- Nama Huruf Kapital Tebal & Subtitle / Role -->
                            <div class="hidden sm:flex flex-col text-left min-w-0 select-none">
                                <span class="text-xs sm:text-[13px] font-bold text-[#0F3A5F] dark:text-[#F1F5F9] uppercase tracking-tight truncate leading-tight max-w-[140px] md:max-w-[220px]">
                                    {{ user?.name || 'Administrator Utama' }}
                                </span>
                                <span class="text-[11px] sm:text-xs text-[#64748B] dark:text-[#94A3B8] font-medium leading-tight mt-0.5 truncate max-w-[140px] md:max-w-[220px]">
                                    {{ userRoleLabel }}
                                </span>
                            </div>

                            <!-- Icon Panah Bawah / Chevron Down -->
                            <ChevronDown
                                class="w-4 h-4 text-[#64748B] dark:text-[#94A3B8] group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] transition-transform duration-200 shrink-0"
                                :class="{ 'rotate-180 text-[#2B7CC2] dark:text-[#38BDF8]': isUserMenuOpen }"
                            />
                        </button>

                        <!-- Menu Dropdown -->
                        <Transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="transform scale-95 opacity-0 -translate-y-1"
                            enter-to-class="transform scale-100 opacity-100 translate-y-0"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="transform scale-100 opacity-100 translate-y-0"
                            leave-close-class="transform scale-95 opacity-0 -translate-y-1"
                        >
                            <div
                                v-if="isUserMenuOpen"
                                class="absolute right-0 top-full mt-2 w-64 bg-white dark:bg-[#131F37] rounded-xl shadow-lg border border-[#E2E8F0] dark:border-[#223354] z-50 origin-top-right select-none overflow-hidden"
                            >
                                <!-- Header Info Pengguna: Bersih & Minimalis -->
                                <div class="px-4 py-3 border-b border-[#E2E8F0] dark:border-[#223354]">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-xs font-bold text-[#0F3A5F] dark:text-[#F1F5F9] uppercase tracking-tight truncate">
                                            {{ user?.name || 'Administrator Utama' }}
                                        </p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#EAF4FC] dark:bg-[#1E3A5F]/50 text-[#2B7CC2] dark:text-[#38BDF8] border border-[#BFE3FA]/80 dark:border-[#2B7CC2]/40 shrink-0">
                                            {{ userRoleLabel }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-[#64748B] dark:text-[#94A3B8] truncate mt-0.5">
                                        {{ user?.email || 'admin@smk1gorontalo.sch.id' }}
                                    </p>
                                </div>

                                <!-- Menu Tautan Akun -->
                                <div class="p-1.5 space-y-0.5">
                                    <Link
                                        href="/settings/profile"
                                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium text-[#0F3A5F] dark:text-[#E2E8F0] hover:text-[#2B7CC2] dark:hover:text-[#38BDF8] hover:bg-[#EAF4FC] dark:hover:bg-[#1E293B] transition-colors cursor-pointer group"
                                        @click="isUserMenuOpen = false"
                                    >
                                        <User class="w-4 h-4 text-[#64748B] dark:text-[#94A3B8] group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] transition-colors shrink-0" />
                                        <span>Profil Pengguna</span>
                                    </Link>

                                    <!-- Menu Keamanan & Sandi (Hanya tampil untuk non-admin, admin mengelola lewat modul Manajemen Pengguna) -->
                                    <Link
                                        v-if="!isAdmin"
                                        href="/settings/security"
                                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium text-[#0F3A5F] dark:text-[#E2E8F0] hover:text-[#2B7CC2] dark:hover:text-[#38BDF8] hover:bg-[#EAF4FC] dark:hover:bg-[#1E293B] transition-colors cursor-pointer group"
                                        @click="isUserMenuOpen = false"
                                    >
                                        <Shield class="w-4 h-4 text-[#64748B] dark:text-[#94A3B8] group-hover:text-[#2B7CC2] dark:group-hover:text-[#38BDF8] transition-colors shrink-0" />
                                        <span>Keamanan & Sandi</span>
                                    </Link>
                                </div>

                                <div class="h-px bg-[#E2E8F0] dark:bg-[#223354] my-0.5 mx-2" />

                                <!-- Tombol Keluar dari Aplikasi -->
                                <div class="p-1.5">
                                    <button
                                        type="button"
                                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer group text-left"
                                        @click="openLogoutModal"
                                    >
                                        <LogOut class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" />
                                        <span>Keluar dari Aplikasi</span>
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- Page Main Body (Satu-satunya area yang bergulir saat modul panjang) -->
            <main class="flex-1 min-h-0 p-4 sm:p-6 overflow-y-auto print:p-0 print:overflow-visible">
                <slot />
            </main>
        </div>

        <!-- Floating Toast Notification (Bersih, Elegan, Auto-Dismiss Beberapa Detik) -->
        <AppToast
            :open="toastState.open"
            :type="toastState.type"
            :title="toastState.title"
            :message="toastState.message"
            :duration="toastState.duration"
            @close="closeToast"
        />

        <!-- Modal Konfirmasi Logout -->
        <LogoutModal
            :open="isLogoutModalOpen"
            @close="isLogoutModalOpen = false"
        />
    </div>
</template>
