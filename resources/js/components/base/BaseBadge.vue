<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    variant?: 'primary' | 'success' | 'warning' | 'danger' | 'neutral';
    size?: 'sm' | 'md';
    dot?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'neutral',
    size: 'md',
    dot: false,
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-[#EAF4FC] dark:bg-[#1E3A5F]/50 text-[#2B7CC2] dark:text-[#38BDF8] border-[#BFE3FA] dark:border-[#2B7CC2]/40';
        case 'success':
            return 'bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#059669] dark:text-emerald-400 border-[#A7F3D0] dark:border-emerald-800/40';
        case 'warning':
            return 'bg-[#FFFBEB] dark:bg-amber-950/40 text-[#D97706] dark:text-amber-400 border-[#FDE68A] dark:border-amber-800/40';
        case 'danger':
            return 'bg-[#FFF1F2] dark:bg-red-950/40 text-[#E11D48] dark:text-red-400 border-[#FECDD3] dark:border-red-800/40';
        case 'neutral':
        default:
            return 'bg-[#F1F5F9] dark:bg-slate-800 text-[#64748B] dark:text-slate-300 border-[#E2E8F0] dark:border-slate-700';
    }
});

const dotClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-[#2B7CC2] dark:bg-[#38BDF8]';
        case 'success':
            return 'bg-[#059669] dark:bg-emerald-400';
        case 'warning':
            return 'bg-[#D97706] dark:bg-amber-400';
        case 'danger':
            return 'bg-[#E11D48] dark:bg-red-400';
        case 'neutral':
        default:
            return 'bg-[#64748B] dark:bg-slate-400';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-2 py-0.5 text-[10px] gap-1';
        case 'md':
        default:
            return 'px-2.5 py-0.5 text-[11px] gap-1.5';
    }
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center font-medium rounded-[6px] border select-none tracking-tight',
            variantClasses,
            sizeClasses,
        ]"
    >
        <span v-if="dot" :class="['w-1.5 h-1.5 rounded-full shrink-0', dotClasses]" />
        <slot />
    </span>
</template>
