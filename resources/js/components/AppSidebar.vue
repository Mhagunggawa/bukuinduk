<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Users,
    FileSpreadsheet,
    Database,
    UserCog,
    X,
} from '@lucide/vue';
import type { NavItem } from '@/types';

export interface NavSection {
    title: string;
    items: NavItem[];
}

interface Props {
    open?: boolean;
    collapsed?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    open: false,
    collapsed: false,
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'toggle-collapse'): void;
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

// 1. Kumpulan Pintasan Navigasi Utama
const mainNavItems = computed<NavItem[]>(() => [
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
]);

// 2. Kumpulan Pintasan Master & Pengguna (Khusus Admin / Super Admin)
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
    <!-- Mobile Backdrop -->
    <div
        v-if="open"
        class="fixed inset-0 z-40 bg-slate-900/30 backdrop-blur-xs md:hidden"
        @click="closeSidebar"
    />

    <!-- Sidebar: Clean White (#FFFFFF), Border Kanan Tipis (#E2E8F0) / Dark Mode Support -->
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 bg-white dark:bg-[#131F37] text-[#0F3A5F] dark:text-[#F1F5F9] flex flex-col transition-all duration-200 ease-in-out md:static md:translate-x-0 print:hidden border-r border-[#E2E8F0] dark:border-[#223354] shadow-sm md:shadow-none h-full shrink-0',
            open ? 'translate-x-0' : '-translate-x-full',
            collapsed ? 'w-20' : 'w-64',
        ]"
    >
        <!-- Header Sidebar -->
        <div
            :class="[
                'h-16 flex items-center border-b border-[#E2E8F0] dark:border-[#223354] bg-white dark:bg-[#131F37] shrink-0 transition-all',
                collapsed ? 'justify-center px-2' : 'justify-between px-4',
            ]"
        >
            <Link
                href="/dashboard"
                class="flex items-center gap-3.5 overflow-hidden select-none group"
                title="SI KARANJI - Buku Induk SMK"
            >
                <!-- Logo Maskot SI KARANJI -->
                <div class="shrink-0 flex items-center justify-center">
                    <img
                        src="/images/logo-full.webp"
                        alt="SI KARANJI"
                        :class="collapsed ? 'w-9 h-9 object-contain' : 'w-10.5 h-10.5 object-contain'"
                        loading="eager"
                    />
                </div>

                <!-- Teks SI KARANJI (HTML text, lebih besar & jelas) -->
                <div v-if="!collapsed" class="flex flex-col min-w-0">
                    <span class="font-bold text-base sm:text-[17px] tracking-tight text-[#0F3A5F] dark:text-[#F1F5F9] leading-none">
                        SI KARANJI
                    </span>
                    <span class="text-xs sm:text-[10.7px] text-[#64748B] dark:text-[#94A3B8] font-medium tracking-tight leading-none mt-1">
                        Buku Induk SMK Negeri 1 Gorontalo
                    </span>
                </div>
            </Link>

            <!-- Tombol Tutup Mobile (Hanya muncul di Mobile Drawer) -->
            <button
                type="button"
                class="md:hidden text-slate-400 hover:text-[#0F3A5F] dark:hover:text-white p-1.5 rounded-md hover:bg-slate-100 dark:hover:bg-[#1E293B] transition-colors cursor-pointer"
                title="Tutup Menu"
                aria-label="Tutup Menu"
                @click="closeSidebar"
            >
                <X class="w-5 h-5" />
            </button>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-5">
            <div
                v-for="(section, sIdx) in navSections"
                :key="section.title"
                :class="['space-y-1', sIdx > 0 ? 'pt-4 border-t border-[#E2E8F0] dark:border-[#223354]' : '']"
            >
                <!-- Label Kelompok Menu -->
                <div
                    v-if="!collapsed"
                    class="px-2.5 pb-1.5 text-[10px] font-bold text-[#64748B] dark:text-[#94A3B8] uppercase tracking-wider select-none"
                >
                    {{ section.title }}
                </div>

                <!-- Item Menu -->
                <div class="space-y-0.5">
                    <Link
                        v-for="item in section.items"
                        :key="item.title"
                        :href="item.href"
                        :title="item.title"
                        :aria-label="item.title"
                        :class="[
                            'flex items-center gap-3 px-2.5 py-2 rounded-[8px] text-xs transition-colors group relative select-none',
                            isNavActive(String(item.href))
                                ? 'bg-[#EAF4FC] dark:bg-[#1E3A5F]/50 text-[#2B7CC2] dark:text-[#38BDF8] font-semibold border-l-[3px] border-[#2B7CC2] dark:border-[#38BDF8] rounded-l-none pl-[9px]'
                                : 'text-[#64748B] dark:text-[#94A3B8] hover:text-[#0F3A5F] dark:hover:text-[#F1F5F9] hover:bg-[#F8FAFC] dark:hover:bg-[#1E293B]',
                            collapsed ? 'justify-center px-0' : '',
                        ]"
                    >
                        <component
                            :is="item.icon"
                            v-if="item.icon"
                            :class="[
                                'w-4 h-4 shrink-0 transition-colors',
                                isNavActive(String(item.href))
                                    ? 'text-[#2B7CC2] dark:text-[#38BDF8]'
                                    : 'text-[#64748B] dark:text-[#94A3B8] group-hover:text-[#0F3A5F] dark:group-hover:text-[#F1F5F9]',
                            ]"
                        />
                        <span v-if="!collapsed" class="truncate">{{ item.title }}</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Bagian Bawah Sidebar: Identitas Aplikasi & Versi -->
        <div class="p-4 border-t border-[#E2E8F0] dark:border-[#223354] bg-white dark:bg-[#131F37] shrink-0">
            <div v-if="!collapsed" class="space-y-1 text-left select-none">
                <h4 class="text-xs font-bold text-[#0F3A5F] dark:text-[#F1F5F9] tracking-wide uppercase">
                    SI KARANJI
                </h4>
                <p class="text-[11px] text-[#64748B] dark:text-[#94A3B8] leading-snug">
                    Sistem Informasi Buku Induk Siswa SMK
                    Kelola Arsip, Rekap Administrasi, Nilai, dan Jejak Induk
                </p>
                <p class="text-[11px] text-[#94A3B8] dark:text-[#64748B] font-normal pt-0.5">
                    Versi 1.0.0
                </p>
            </div>
            <div v-else class="text-center py-1 select-none" title="SI KARANJI - Versi 1.0.0">
                <span class="font-bold text-xs text-[#0F3A5F] dark:text-[#F1F5F9] block">SK</span>
                <span class="text-[9px] text-[#64748B] dark:text-[#94A3B8] block">v1.0</span>
            </div>
        </div>
    </aside>
</template>
