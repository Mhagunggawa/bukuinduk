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
    }
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
            }
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
            'fixed inset-y-0 left-0 z-50 w-64 bg-[#0B1736] text-white flex flex-col transition-transform duration-300 ease-in-out md:static md:translate-x-0 print:hidden shadow-2xl border-r border-slate-800/60',
            open ? 'translate-x-0' : '-translate-x-full',
        ]"
    >
        <!-- Sidebar Header / Logo -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-white/10 bg-[#070e24]/70 shrink-0">
            <Link href="/dashboard" class="flex items-center gap-3 group" title="E-Buku Induk SMK">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 to-sky-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/25 ring-2 ring-white/15 group-hover:scale-105 transition-all">
                    <GraduationCap class="w-5 h-5" />
                </div>
                <div class="flex flex-col">
                    <span class="font-black text-sm tracking-wide text-white leading-tight uppercase">E-Buku Induk</span>
                    <span class="text-[10px] text-sky-300/90 font-semibold tracking-wider">SMK AKADEMIK</span>
                </div>
            </Link>
            <button
                class="md:hidden text-slate-400 hover:text-white p-1 rounded-lg cursor-pointer"
                title="Tutup Menu"
                @click="closeSidebar"
            >
                <X class="w-5 h-5" />
            </button>
        </div>

        <!-- Navigation Links (Kumpulan Pintasan Berdasarkan Tiap Bagian Title) -->
        <div class="flex-1 overflow-y-auto py-5 px-3 space-y-6">
            <div
                v-for="(section, sIdx) in navSections"
                :key="section.title"
                :class="['space-y-1.5', sIdx > 0 ? 'border-t border-white/10 pt-5' : '']"
            >
                <!-- Title Tiap Bagian Sidebar (Jelas, Terbaca, Bersih) -->
                <div
                    class="px-3 pb-1 flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider"
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
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group',
                            isNavActive(String(item.href))
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold'
                                : 'text-slate-300 hover:bg-white/5 hover:text-white',
                        ]"
                    >
                        <component
                            :is="item.icon"
                            v-if="item.icon"
                            :class="[
                                'w-4 h-4 shrink-0 transition-colors',
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
        <div class="p-3 border-t border-white/10 bg-[#070e24]/80 shrink-0">
            <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0 ring-1 ring-white/20">
                        {{ userInitials }}
                    </div>
                    <div class="min-w-0 flex flex-col">
                        <span class="text-xs font-bold text-white truncate leading-tight">{{ user?.name || 'Pengguna' }}</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                            <span class="text-[10px] text-slate-300 uppercase font-semibold">
                                {{ user?.role === 'admin' ? 'Administrator' : 'Tata Usaha' }}
                            </span>
                        </div>
                    </div>
                </div>

                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-white/10 rounded-lg transition-colors cursor-pointer"
                    title="Keluar dari Aplikasi"
                >
                    <LogOut class="w-4 h-4" />
                </Link>
            </div>
        </div>
    </aside>
</template>
