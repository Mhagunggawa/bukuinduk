<script setup lang="ts">
import { computed } from 'vue';
import {
    CheckCircle2,
    AlertCircle,
    AlertTriangle,
    Info,
    X,
} from '@lucide/vue';

export interface BaseAlertProps {
    type?: 'success' | 'error' | 'warning' | 'info';
    title?: string;
    message?: string;
    dismissible?: boolean;
}

const props = withDefaults(defineProps<BaseAlertProps>(), {
    type: 'info',
    title: '',
    message: '',
    dismissible: false,
});

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const config = computed(() => {
    switch (props.type) {
        case 'success':
            return {
                container: 'bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-200/80 dark:border-emerald-800/40 text-emerald-900 dark:text-emerald-200',
                iconBg: 'bg-emerald-100/80 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300',
                icon: CheckCircle2,
                title: 'text-emerald-950 dark:text-emerald-100',
                text: 'text-emerald-800 dark:text-emerald-300/90',
            };
        case 'error':
            return {
                container: 'bg-rose-50/70 dark:bg-rose-950/30 border-rose-200/80 dark:border-rose-800/40 text-rose-900 dark:text-rose-200',
                iconBg: 'bg-rose-100/80 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300',
                icon: AlertCircle,
                title: 'text-rose-950 dark:text-rose-100',
                text: 'text-rose-800 dark:text-rose-300/90',
            };
        case 'warning':
            return {
                container: 'bg-amber-50/70 dark:bg-amber-950/30 border-amber-200/80 dark:border-amber-800/40 text-amber-900 dark:text-amber-200',
                iconBg: 'bg-amber-100/80 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
                icon: AlertTriangle,
                title: 'text-amber-950 dark:text-amber-100',
                text: 'text-amber-800 dark:text-amber-300/90',
            };
        case 'info':
        default:
            return {
                container: 'bg-[#EAF4FC]/70 dark:bg-[#1E3A5F]/30 border-[#BFE3FA] dark:border-[#2B7CC2]/40 text-[#0F3A5F] dark:text-[#F1F5F9]',
                iconBg: 'bg-[#BFE3FA]/60 dark:bg-[#1E3A5F]/60 text-[#2B7CC2] dark:text-[#38BDF8]',
                icon: Info,
                title: 'text-[#0F3A5F] dark:text-[#F1F5F9]',
                text: 'text-slate-600 dark:text-[#94A3B8]',
            };
    }
});
</script>

<template>
    <div
        class="rounded-xl border p-3.5 sm:p-4 transition-all duration-200 flex items-start gap-3 shadow-2xs"
        :class="config.container"
        role="alert"
    >
        <div
            class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
            :class="config.iconBg"
        >
            <component :is="config.icon" class="w-4 h-4" />
        </div>

        <div class="flex-1 min-w-0">
            <h5 v-if="title" class="text-xs sm:text-[13px] font-bold tracking-tight" :class="config.title">
                {{ title }}
            </h5>
            <div v-if="message" class="text-xs leading-relaxed" :class="[config.text, { 'mt-0.5': title }]">
                {{ message }}
            </div>
            <slot />
        </div>

        <button
            v-if="dismissible"
            type="button"
            class="p-1 -mr-1 -mt-1 rounded-md text-current opacity-60 hover:opacity-100 transition-opacity cursor-pointer shrink-0"
            title="Tutup"
            aria-label="Tutup"
            @click="emit('close')"
        >
            <X class="w-4 h-4" />
        </button>
    </div>
</template>
