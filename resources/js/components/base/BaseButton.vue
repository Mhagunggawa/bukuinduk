<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';

interface Props {
    variant?: 'primary' | 'secondary' | 'outline' | 'danger' | 'danger-soft' | 'ghost';
    size?: 'sm' | 'md' | 'lg';
    type?: 'button' | 'submit' | 'reset';
    loading?: boolean;
    disabled?: boolean;
    href?: string;
    as?: any;
    external?: boolean;
    download?: boolean | string;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'md',
    type: 'button',
    loading: false,
    disabled: false,
    href: undefined,
    as: undefined,
    external: false,
    download: false,
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-white dark:bg-[#1E293B] hover:bg-slate-50 dark:hover:bg-[#283548] text-[#0F3A5F] dark:text-[#F1F5F9] border border-[#E2E8F0] dark:border-[#223354] shadow-xs active:bg-slate-100 dark:active:bg-[#334155]';
        case 'outline':
            return 'bg-transparent hover:bg-[#EAF4FC] dark:hover:bg-[#1E3A5F]/40 text-[#2B7CC2] dark:text-[#38BDF8] border border-[#BFE3FA] dark:border-[#2B7CC2]/60 active:bg-[#EAF4FC]/80';
        case 'danger':
            return 'bg-red-600 hover:bg-red-700 text-white border border-transparent shadow-xs active:bg-red-800 font-medium';
        case 'danger-soft':
            return 'bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-900/60 active:bg-red-200 font-medium';
        case 'ghost':
            return 'bg-transparent hover:bg-slate-100 dark:hover:bg-[#1E293B] text-[#0F3A5F] dark:text-[#F1F5F9] border-transparent active:bg-slate-200 dark:active:bg-[#283548]';
        case 'primary':
        default:
            return 'bg-[#2B7CC2] hover:bg-[#226AA8] dark:bg-[#2563EB] dark:hover:bg-[#1D4ED8] text-white border border-transparent shadow-xs active:bg-[#1E5D94]';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'h-8 px-3 text-xs gap-1.5 rounded-[8px]';
        case 'lg':
            return 'h-11 px-5 text-sm gap-2.5 rounded-[10px]';
        case 'md':
        default:
            return 'h-9 px-4 text-xs sm:text-sm gap-2 rounded-[8px]';
    }
});
</script>

<template>
    <a
        v-if="href && (external || download || as === 'a')"
        :href="href"
        :download="typeof download === 'string' ? download : (download ? '' : undefined)"
        :class="[
            'inline-flex items-center justify-center font-medium transition-colors select-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#BFE3FA] disabled:opacity-50 disabled:pointer-events-none cursor-pointer',
            variantClasses,
            sizeClasses,
        ]"
    >
        <Loader2 v-if="loading" class="w-3.5 h-3.5 animate-spin" />
        <slot />
    </a>
    <Link
        v-else-if="href"
        :href="href"
        :as="as"
        :class="[
            'inline-flex items-center justify-center font-medium transition-colors select-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#BFE3FA] disabled:opacity-50 disabled:pointer-events-none cursor-pointer',
            variantClasses,
            sizeClasses,
        ]"
    >
        <Loader2 v-if="loading" class="w-3.5 h-3.5 animate-spin" />
        <slot />
    </Link>
    <button
        v-else
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'inline-flex items-center justify-center font-medium transition-colors select-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#BFE3FA] disabled:opacity-50 disabled:pointer-events-none cursor-pointer',
            variantClasses,
            sizeClasses,
        ]"
    >
        <Loader2 v-if="loading" class="w-3.5 h-3.5 animate-spin" />
        <slot />
    </button>
</template>
