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
    },
);

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const activeTahunPelajaran = computed(
    () => (page.props as any).activeTahunPelajaran || '2025/2026',
);
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
        return activeBreadcrumbs.value[activeBreadcrumbs.value.length - 1]
            .title;
    }
    return resolvedRoute.value.pageTitle;
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-slate-50/70 font-sans text-slate-900 antialiased md:flex-row"
    >
        <!-- AppSidebar Component (Kumpulan Pintasan & Navigasi Sidebar) -->
        <AppSidebar :open="isSidebarOpen" @close="closeSidebar" />

        <!-- Right Main Viewport -->
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <!-- Modern Topbar (bg-white/95 backdrop-blur-md border-b border-slate-200/80 min-h-16 py-2.5 px-4 sm:px-6 flex items-center justify-between shrink-0 sticky top-0 z-30 shadow-2xs print:hidden) -->
            <header
                class="sticky top-0 z-30 flex min-h-16 shrink-0 items-center justify-between border-b border-slate-200/80 bg-white/95 px-4 py-2 shadow-2xs backdrop-blur-md sm:px-6 print:hidden"
            >
                <div class="flex min-w-0 items-center gap-3.5">
                    <button
                        type="button"
                        class="shrink-0 cursor-pointer rounded-lg p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 md:hidden"
                        title="Buka Menu"
                        @click="toggleSidebar"
                    >
                        <Menu class="h-5 w-5" />
                    </button>

                    <!-- Page Title & Breadcrumbs Section -->
                    <div class="flex min-w-0 flex-col justify-center">
                        <!-- Breadcrumbs Trail -->
                        <nav
                            class="flex items-center text-[11px] font-semibold text-slate-400"
                        >
                            <Link
                                href="/dashboard"
                                class="flex items-center gap-1 text-slate-500 hover:text-blue-900 hover:underline"
                            >
                                <span>Beranda</span>
                            </Link>
                            <template
                                v-for="(crumb, idx) in activeBreadcrumbs"
                                :key="idx"
                            >
                                <ChevronRight
                                    class="mx-1 h-3 w-3 shrink-0 text-slate-400"
                                />
                                <Link
                                    v-if="
                                        crumb.href &&
                                        idx < activeBreadcrumbs.length - 1
                                    "
                                    :href="crumb.href"
                                    class="max-w-[120px] truncate text-slate-500 hover:text-blue-900 hover:underline sm:max-w-none"
                                >
                                    {{ crumb.title }}
                                </Link>
                                <span
                                    v-else
                                    :class="
                                        idx === activeBreadcrumbs.length - 1
                                            ? 'max-w-[180px] truncate font-bold text-blue-900 sm:max-w-none'
                                            : 'max-w-[120px] truncate text-slate-500 sm:max-w-none'
                                    "
                                >
                                    {{ crumb.title }}
                                </span>
                            </template>
                        </nav>

                        <!-- Prominent Active Page Title Header -->
                        <div class="mt-0.5 flex items-center gap-2">
                            <h2
                                class="truncate text-sm leading-tight font-extrabold tracking-tight text-blue-950 sm:text-base"
                            >
                                {{ activePageTitle }}
                            </h2>
                            <span
                                v-if="resolvedRoute.sectionTitle"
                                class="hidden items-center rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-bold tracking-wider text-slate-500 uppercase lg:inline-flex"
                            >
                                {{ resolvedRoute.sectionTitle }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Topbar Controls -->
                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <!-- Active TP Chip -->
                    <div
                        class="hidden items-center gap-1.5 rounded-full border border-blue-200/80 bg-blue-50/80 px-3 py-1 text-xs font-bold text-blue-900 shadow-2xs sm:flex"
                    >
                        <Calendar class="h-3.5 w-3.5 text-blue-600" />
                        <span>T.P. {{ activeTahunPelajaran }}</span>
                    </div>

                    <!-- Role Badge Chip -->
                    <span
                        v-if="user?.role === 'admin'"
                        class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800 shadow-2xs"
                    >
                        <Shield class="h-3 w-3 text-emerald-600" />
                        Admin
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1 rounded-full border border-blue-300 bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-800 shadow-2xs"
                    >
                        <UserCheck class="h-3 w-3 text-blue-600" />
                        Tata Usaha
                    </span>
                </div>
            </header>

            <!-- Notification Flash Banners -->
            <div
                v-if="flash?.success || flash?.error"
                class="p-4 pt-4 pb-0 sm:px-6 print:hidden"
            >
                <div
                    v-if="flash?.success"
                    class="flex items-start gap-2.5 rounded-xl border border-emerald-300 bg-emerald-50/90 p-3.5 text-xs font-semibold text-emerald-950 shadow-xs backdrop-blur-xs"
                >
                    <CheckCircle2
                        class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"
                    />
                    <div class="flex-1">{{ flash.success }}</div>
                </div>
                <div
                    v-if="flash?.error"
                    class="mt-2 flex items-start gap-2.5 rounded-xl border border-rose-300 bg-rose-50/90 p-3.5 text-xs font-semibold text-rose-950 shadow-xs backdrop-blur-xs"
                >
                    <AlertCircle
                        class="mt-0.5 h-4 w-4 shrink-0 text-rose-600"
                    />
                    <div class="flex-1">{{ flash.error }}</div>
                </div>
            </div>

            <!-- Page Main Body -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
