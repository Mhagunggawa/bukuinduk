<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Users,
    FileSpreadsheet,
    Database,
    UserCog,
    GraduationCap,
    LogOut,
    X,
} from '@lucide/vue';
import type { NavItem } from '@/types';

export interface NavSection {
    title: string;
    items: NavItem[];
}

withDefaults(
    defineProps<{
        open?: boolean;
    }>(),
    {
        open: false,
    },
);

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const currentUrl = computed(() => page.url);

const isNavActive = (path: string) => {
    if (path === '/dashboard') {
        return currentUrl.value === '/dashboard' || currentUrl.value === '/';
    }
    return currentUrl.value.startsWith(path);
};

// 1. Kumpulan Pintasan Navigasi Utama (computed NavItem[])
const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard' as any,
            icon: LayoutDashboard,
        },
        {
            title: 'Direktori Siswa',
            href: '/siswa' as any,
            icon: Users,
        },
        {
            title: 'Import Data Massal',
            href: '/import' as any,
            icon: FileSpreadsheet,
        },
    ];
    return items;
});

// 2. Kumpulan Pintasan Master & Pengguna (computed NavItem[] - Khusus Role Admin / Super Admin)
const masterNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];
    const role = (page.props.auth as any)?.user?.role;
    if (['admin', 'super admin'].includes(role)) {
        items.push(
            {
                title: 'Data Master',
                href: '/master-data' as any,
                icon: Database,
            },
            {
                title: 'Manajemen Pengguna',
                href: '/users' as any,
                icon: UserCog,
            },
        );
    }
    return items;
});

// 3. Kumpulan Bagian / Section pada AppSidebar dengan Title masing-masing
const navSections = computed<NavSection[]>(() => {
    const sections: NavSection[] = [
        {
            title: 'Navigasi Utama',
            items: mainNavItems.value,
        },
    ];

    if (masterNavItems.value.length > 0) {
        sections.push({
            title: 'Master & Pengguna',
            items: masterNavItems.value,
        });
    }

    return sections;
});

const userInitials = computed(() => {
    const name = user.value?.name || 'User';
    return name
        .split(' ')
        .map((n: string) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const closeSidebar = () => {
    emit('close');
};
</script>

<template>
    <!-- Mobile Sidebar Backdrop -->
    <div
        v-if="open"
        class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs md:hidden"
        @click="closeSidebar"
    />

    <!-- Sidebar: Clean Modern Deep Navy (w-64 bg-[#0B1736] text-white) -->
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-800/60 bg-[#0B1736] text-white shadow-2xl transition-transform duration-300 ease-in-out md:static md:translate-x-0 print:hidden',
            open ? 'translate-x-0' : '-translate-x-full',
        ]"
    >
        <!-- Sidebar Header / Logo -->
        <div
            class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 bg-[#070e24]/70 px-5"
        >
            <Link
                href="/dashboard"
                class="group flex items-center gap-3"
                title="E-Buku Induk SMK"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-blue-700 to-sky-500 text-white shadow-lg ring-2 shadow-blue-500/25 ring-white/15 transition-all group-hover:scale-105"
                >
                    <GraduationCap class="h-5 w-5" />
                </div>
                <div class="flex flex-col">
                    <span
                        class="text-sm leading-tight font-black tracking-wide text-white uppercase"
                        >E-Buku Induk</span
                    >
                    <span
                        class="text-[10px] font-semibold tracking-wider text-sky-300/90"
                        >SMK AKADEMIK</span
                    >
                </div>
            </Link>
            <button
                class="cursor-pointer rounded-lg p-1 text-slate-400 hover:text-white md:hidden"
                title="Tutup Menu"
                @click="closeSidebar"
            >
                <X class="h-5 w-5" />
            </button>
        </div>

        <!-- Navigation Links (Kumpulan Pintasan Berdasarkan Tiap Bagian Title) -->
        <div class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
            <div
                v-for="(section, sIdx) in navSections"
                :key="section.title"
                :class="[
                    'space-y-1.5',
                    sIdx > 0 ? 'border-t border-white/10 pt-5' : '',
                ]"
            >
                <!-- Title Tiap Bagian Sidebar (Jelas, Terbaca, Bersih) -->
                <div
                    class="flex items-center justify-between px-3 pb-1 text-[11px] font-bold tracking-wider text-slate-400 uppercase"
                    :title="section.title"
                >
                    <span>{{ section.title }}</span>
                </div>

                <!-- Daftar Pintasan dalam Bagian Tersebut -->
                <div class="space-y-1">
                    <Link
                        v-for="item in section.items"
                        :key="item.title"
                        :href="item.href"
                        :title="item.title"
                        :aria-label="item.title"
                        :class="[
                            'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all',
                            isNavActive(String(item.href))
                                ? 'bg-blue-600 font-bold text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-300 hover:bg-white/5 hover:text-white',
                        ]"
                    >
                        <component
                            :is="item.icon"
                            v-if="item.icon"
                            :class="[
                                'h-4 w-4 shrink-0 transition-colors',
                                isNavActive(String(item.href))
                                    ? 'text-white'
                                    : section.title.includes('Master')
                                      ? 'text-amber-400 group-hover:text-amber-300'
                                      : 'text-slate-400 group-hover:text-sky-400',
                            ]"
                        />
                        <span class="truncate">{{ item.title }}</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Modern User Card at Sidebar Bottom -->
        <div class="shrink-0 border-t border-white/10 bg-[#070e24]/80 p-3">
            <div
                class="flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-white/5 p-2.5"
            >
                <div class="flex min-w-0 items-center gap-2.5">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 text-xs font-bold text-white ring-1 ring-white/20"
                    >
                        {{ userInitials }}
                    </div>
                    <div class="flex min-w-0 flex-col">
                        <span
                            class="truncate text-xs leading-tight font-bold text-white"
                            >{{ user?.name || 'Pengguna' }}</span
                        >
                        <div class="mt-0.5 flex items-center gap-1.5">
                            <span
                                class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"
                            ></span>
                            <span
                                class="text-[10px] font-semibold text-slate-300 uppercase"
                            >
                                {{
                                    user?.role === 'admin'
                                        ? 'Administrator'
                                        : 'Tata Usaha'
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="cursor-pointer rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-white/10 hover:text-rose-400"
                    title="Keluar dari Aplikasi"
                >
                    <LogOut class="h-4 w-4" />
                </Link>
            </div>
        </div>
    </aside>
</template>
