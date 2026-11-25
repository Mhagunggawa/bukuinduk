<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    title?: string;
    subtitle?: string;
    padding?: 'none' | 'sm' | 'md' | 'lg';
    hoverable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: undefined,
    subtitle: undefined,
    padding: 'md',
    hoverable: false,
});

const paddingClasses = computed(() => {
    switch (props.padding) {
        case 'none':
            return '';
        case 'sm':
            return 'p-3.5';
        case 'lg':
            return 'p-6 sm:p-8';
        case 'md':
        default:
            return 'p-5';
    }
});
</script>

<template>
    <div
        :class="[
            'bg-white dark:bg-[#131F37] rounded-[10px] border border-[#E2E8F0] dark:border-[#223354] shadow-[0_1px_3px_0_rgba(15,58,95,0.04)] dark:shadow-none transition-all',
            hoverable ? 'hover:border-[#BFE3FA] dark:hover:border-[#38BDF8]/40 hover:shadow-[0_4px_6px_-1px_rgba(15,58,95,0.06)]' : '',
        ]"
    >
        <div
            v-if="title || $slots.header || $slots['header-actions']"
            class="px-5 py-4 border-b border-[#E2E8F0] dark:border-[#223354] flex items-center justify-between gap-4"
        >
            <div v-if="title || subtitle" class="min-w-0">
                <slot name="header">
                    <h3 class="text-sm font-bold text-[#0F3A5F] dark:text-[#F1F5F9] tracking-tight">
                        {{ title }}
                    </h3>
                    <p v-if="subtitle" class="text-xs text-[#64748B] dark:text-[#94A3B8] mt-0.5">
                        {{ subtitle }}
                    </p>
                </slot>
            </div>
            <div v-if="$slots['header-actions']" class="shrink-0 flex items-center gap-2">
                <slot name="header-actions" />
            </div>
        </div>

        <div :class="paddingClasses">
            <slot />
        </div>

        <div
            v-if="$slots.footer"
            class="px-5 py-3.5 bg-[#F8FAFC] dark:bg-[#0E1726] border-t border-[#E2E8F0] dark:border-[#223354] rounded-b-[9px] flex items-center justify-between"
        >
            <slot name="footer" />
        </div>
    </div>
</template>
