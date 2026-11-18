<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppSidebar from '@/components/AppSidebar.vue';
import {
    Menu,
    ChevronRight,
    Calendar,
    CheckCircle2,
    AlertCircle,
    Shield,
    UserCheck,
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
const activeTahunPelajaran = computed(() => (page.props as any).activeTahunPelajaran || '2025/2026');
const flash = computed(() => (page.props as any).flash || {});

const isSidebarOpen = ref(false);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebar = () => {
    isSidebarOpen.value = false;
};

const currentUrl = computed(() => page.url);

// Resolusi otomatis section, page title, dan breadcrumbs jika tidak diberikan via props
const resolvedRoute = computed(() => {
    const url = currentUrl.value;

    if (url === '/dashboard' || url === '/') {
        return {
            sectionTitle: 'Navigasi Utama',
            pageTitle: 'Dashboard',
            breadcrumbs: [{ title: 'Dashboard', href: '/dashboard' }],
        };
    }
    if (url.startsWith('/siswa') && url.includes('/buku-induk')) {
        return {
            sectionTitle: 'Navigasi Utama',
            pageTitle: 'Buku Induk Siswa',
            breadcrumbs: [
                { title: 'Direktori Siswa', href: '/siswa' },
                { title: 'Buku Induk Siswa' },
            ],
        };
    }
    if (url.startsWith('/siswa')) {
        return {
            sectionTitle: 'Navigasi Utama',
            pageTitle: 'Direktori Siswa',
            breadcrumbs: [{ title: 'Direktori Siswa', href: '/siswa' }],
        };
    }
    if (url.startsWith('/import')) {
        return {
            sectionTitle: 'Navigasi Utama',
            pageTitle: 'Import Data Massal',
            breadcrumbs: [{ title: 'Import Data Massal', href: '/import' }],
        };
    }
    if (url.startsWith('/master-data')) {
        return {
            sectionTitle: 'Master & Pengguna',
            pageTitle: 'Data Master',
            breadcrumbs: [
                { title: 'Master & Pengguna' },
                { title: 'Data Master', href: '/master-data' },
            ],
        };
    }
    if (url.startsWith('/users')) {
        return {
            sectionTitle: 'Master & Pengguna',
            pageTitle: 'Manajemen Pengguna',
            breadcrumbs: [
                { title: 'Master & Pengguna' },
                { title: 'Manajemen Pengguna', href: '/users' },
            ],
        };
    }

    return {
        sectionTitle: '',
        pageTitle: 'Halaman',
        breadcrumbs: [],
    };
});

// Breadcrumbs yang aktif: utamakan props.breadcrumbs jika diberikan, fallback ke resolvedRoute
const activeBreadcrumbs = computed<Breadcrumb[]>(() => {
    if (props.breadcrumbs && props.breadcrumbs.length > 0) {
        return props.breadcrumbs;
    }
    return resolvedRoute.value.breadcrumbs;
});

// Judul halaman aktif di Topbar
const activePageTitle = computed(() => {
    if (activeBreadcrumbs.value.length > 0) {
        return activeBreadcrumbs.value[activeBreadcrumbs.value.length - 1].title;
    }
    return resolvedRoute.value.pageTitle;
});
</script>

<template>
    <div class="min-h-screen bg-slate-50/70 font-sans text-slate-900 flex flex-col md:flex-row antialiased">
        <!-- AppSidebar Component (Kumpulan Pintasan & Navigasi Sidebar) -->
        <AppSidebar :open="isSidebarOpen" @close="closeSidebar" />

        <!-- Right Main Viewport -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Modern Topbar (bg-white/95 backdrop-blur-md border-b border-slate-200/80 min-h-16 py-2.5 px-4 sm:px-6 flex items-center justify-between shrink-0 sticky top-0 z-30 shadow-2xs print:hidden) -->
            <header class="min-h-16 py-2 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-6 flex items-center justify-between shrink-0 sticky top-0 z-30 shadow-2xs print:hidden">
                <div class="flex items-center gap-3.5 min-w-0">
                    <button
                        type="button"
                        class="md:hidden p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 cursor-pointer shrink-0"
                        title="Buka Menu"
                        @click="toggleSidebar"
                    >
                        <Menu class="w-5 h-5" />
                    </button>

                    <!-- Page Title & Breadcrumbs Section -->
                    <div class="flex flex-col justify-center min-w-0">
                        <!-- Breadcrumbs Trail -->
                        <nav class="flex items-center text-[11px] font-semibold text-slate-400">
                            <Link href="/dashboard" class="hover:text-blue-900 flex items-center gap-1 text-slate-500 hover:underline">
                                <span>Beranda</span>
                            </Link>
                            <template v-for="(crumb, idx) in activeBreadcrumbs" :key="idx">
                                <ChevronRight class="w-3 h-3 mx-1 text-slate-400 shrink-0" />
                                <Link
                                    v-if="crumb.href && idx < activeBreadcrumbs.length - 1"
                                    :href="crumb.href"
                                    class="hover:text-blue-900 text-slate-500 hover:underline truncate max-w-[120px] sm:max-w-none"
                                >
                                    {{ crumb.title }}
                                </Link>
                                <span
                                    v-else
                                    :class="idx === activeBreadcrumbs.length - 1 ? 'text-blue-900 font-bold truncate max-w-[180px] sm:max-w-none' : 'text-slate-500 truncate max-w-[120px] sm:max-w-none'"
                                >
                                    {{ crumb.title }}
                                </span>
                            </template>
                        </nav>

                        <!-- Prominent Active Page Title Header -->
                        <div class="flex items-center gap-2 mt-0.5">
                            <h2 class="text-sm sm:text-base font-extrabold text-blue-950 tracking-tight leading-tight truncate">
                                {{ activePageTitle }}
                            </h2>
                            <span
                                v-if="resolvedRoute.sectionTitle"
                                class="hidden lg:inline-flex items-center text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200 uppercase tracking-wider"
                            >
                                {{ resolvedRoute.sectionTitle }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Topbar Controls -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- Active TP Chip -->
                    <div class="hidden sm:flex items-center gap-1.5 bg-blue-50/80 text-blue-900 border border-blue-200/80 px-3 py-1 rounded-full text-xs font-bold shadow-2xs">
                        <Calendar class="w-3.5 h-3.5 text-blue-600" />
                        <span>T.P. {{ activeTahunPelajaran }}</span>
                    </div>

                    <!-- Role Badge Chip -->
                    <span
                        v-if="user?.role === 'admin'"
                        class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-300 text-xs font-bold px-2.5 py-1 rounded-full shadow-2xs"
                    >
                        <Shield class="w-3 h-3 text-emerald-600" />
                        Admin
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1 bg-blue-50 text-blue-800 border border-blue-300 text-xs font-bold px-2.5 py-1 rounded-full shadow-2xs"
                    >
                        <UserCheck class="w-3 h-3 text-blue-600" />
                        Tata Usaha
                    </span>
                </div>
            </header>

            <!-- Notification Flash Banners -->
            <div v-if="flash?.success || flash?.error" class="p-4 sm:px-6 pt-4 pb-0 print:hidden">
                <div
                    v-if="flash?.success"
                    class="bg-emerald-50/90 backdrop-blur-xs border border-emerald-300 text-emerald-950 rounded-xl p-3.5 text-xs font-semibold flex items-start gap-2.5 shadow-xs"
                >
                    <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                    <div class="flex-1">{{ flash.success }}</div>
                </div>
                <div
                    v-if="flash?.error"
                    class="bg-rose-50/90 backdrop-blur-xs border border-rose-300 text-rose-950 rounded-xl p-3.5 text-xs font-semibold flex items-start gap-2.5 shadow-xs mt-2"
                >
                    <AlertCircle class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" />
                    <div class="flex-1">{{ flash.error }}</div>
                </div>
            </div>

            <!-- Page Main Body -->
            <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
