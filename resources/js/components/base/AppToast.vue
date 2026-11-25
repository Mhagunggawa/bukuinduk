<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import {
    CheckCircle2,
    AlertCircle,
    AlertTriangle,
    Info,
    X,
} from '@lucide/vue';

export interface ToastProps {
    open: boolean;
    type?: 'success' | 'error' | 'warning' | 'info';
    title?: string;
    message: string;
    duration?: number; // dalam milidetik, default 5000
    autoClose?: boolean;
}

const props = withDefaults(defineProps<ToastProps>(), {
    type: 'success',
    title: '',
    duration: 5000,
    autoClose: true,
});

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const isPaused = ref(false);
const remainingTime = ref(props.duration);
let timerId: any = null;
let startTime = 0;

const defaultTitle = computed(() => {
    if (props.title) return props.title;
    switch (props.type) {
        case 'success':
            return 'Berhasil';
        case 'error':
            return 'Pemberitahuan Sistem';
        case 'warning':
            return 'Peringatan';
        case 'info':
            return 'Informasi';
        default:
            return 'Notifikasi';
    }
});

const startTimer = () => {
    if (!props.autoClose || remainingTime.value <= 0) return;
    startTime = Date.now();
    clearTimeout(timerId);
    timerId = setTimeout(() => {
        close();
    }, remainingTime.value);
};

const pauseTimer = () => {
    if (!props.autoClose) return;
    isPaused.value = true;
    clearTimeout(timerId);
    const elapsed = Date.now() - startTime;
    remainingTime.value = Math.max(0, remainingTime.value - elapsed);
};

const resumeTimer = () => {
    if (!props.autoClose) return;
    isPaused.value = false;
    startTimer();
};

const close = () => {
    clearTimeout(timerId);
    emit('close');
};

watch(
    () => props.open,
    (val) => {
        if (val) {
            remainingTime.value = props.duration;
            isPaused.value = false;
            startTimer();
        } else {
            clearTimeout(timerId);
        }
    },
    { immediate: true }
);

watch(
    () => props.message,
    () => {
        if (props.open) {
            remainingTime.value = props.duration;
            isPaused.value = false;
            startTimer();
        }
    }
);

onUnmounted(() => {
    clearTimeout(timerId);
});

// Styling berdasarkan varian
const config = computed(() => {
    switch (props.type) {
        case 'error':
            return {
                border: 'border-rose-200/90 dark:border-rose-500/30',
                glow: 'shadow-lg shadow-rose-950/5 dark:shadow-black/40',
                iconBg: 'bg-rose-50 dark:bg-rose-950/50 border border-rose-200/80 dark:border-rose-800/40 text-rose-600 dark:text-rose-400',
                progress: 'bg-rose-500 dark:bg-rose-400',
                titleColor: 'text-rose-900 dark:text-rose-200',
                accentLine: 'bg-rose-500',
            };
        case 'warning':
            return {
                border: 'border-amber-200/90 dark:border-amber-500/30',
                glow: 'shadow-lg shadow-amber-950/5 dark:shadow-black/40',
                iconBg: 'bg-amber-50 dark:bg-amber-950/50 border border-amber-200/80 dark:border-amber-800/40 text-amber-600 dark:text-amber-400',
                progress: 'bg-amber-500 dark:bg-amber-400',
                titleColor: 'text-amber-900 dark:text-amber-200',
                accentLine: 'bg-amber-500',
            };
        case 'info':
            return {
                border: 'border-[#BFE3FA] dark:border-[#2B7CC2]/40',
                glow: 'shadow-lg shadow-[#0F3A5F]/5 dark:shadow-black/40',
                iconBg: 'bg-[#EAF4FC] dark:bg-[#1E3A5F]/50 border border-[#BFE3FA] dark:border-[#2B7CC2]/40 text-[#2B7CC2] dark:text-[#38BDF8]',
                progress: 'bg-[#2B7CC2] dark:bg-[#38BDF8]',
                titleColor: 'text-[#0F3A5F] dark:text-[#F1F5F9]',
                accentLine: 'bg-[#2B7CC2]',
            };
        case 'success':
        default:
            return {
                border: 'border-emerald-200/90 dark:border-emerald-500/30',
                glow: 'shadow-lg shadow-emerald-950/5 dark:shadow-black/40',
                iconBg: 'bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/80 dark:border-emerald-800/40 text-emerald-600 dark:text-emerald-400',
                progress: 'bg-emerald-500 dark:bg-emerald-400',
                titleColor: 'text-emerald-950 dark:text-emerald-200',
                accentLine: 'bg-emerald-500',
            };
    }
});
</script>

<template>
    <!-- Floating Toast Container (Tetap berada di pojok kanan atas, tidak menggeser layout) -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform translate-y-[-16px] sm:translate-y-0 sm:translate-x-8 opacity-0 scale-95"
            enter-to-class="transform translate-y-0 sm:translate-x-0 opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform translate-y-0 sm:translate-x-0 opacity-100 scale-100"
            leave-to-class="transform translate-y-[-12px] sm:translate-y-0 sm:translate-x-6 opacity-0 scale-95"
        >
            <div
                v-if="open"
                class="fixed top-4 right-4 sm:top-5 sm:right-6 z-[100] max-w-[calc(100vw-2rem)] sm:max-w-md w-[380px] pointer-events-auto select-none"
                role="status"
                aria-live="polite"
                @mouseenter="pauseTimer"
                @mouseleave="resumeTimer"
            >
                <!-- Card Notifikasi Bersih & Modern -->
                <div
                    class="relative overflow-hidden rounded-2xl bg-white/95 dark:bg-[#131F37]/95 backdrop-blur-md border p-4 sm:p-4.5 transition-all duration-200 flex items-start gap-3.5"
                    :class="[config.border, config.glow]"
                >
                    <!-- Ikon Simbol Status Bersih -->
                    <div
                        class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                        :class="config.iconBg"
                    >
                        <CheckCircle2 v-if="type === 'success'" class="w-5 h-5" />
                        <AlertCircle v-else-if="type === 'error'" class="w-5 h-5" />
                        <AlertTriangle v-else-if="type === 'warning'" class="w-5 h-5" />
                        <Info v-else class="w-5 h-5" />
                    </div>

                    <!-- Teks Informasi & Judul Notifikasi -->
                    <div class="flex-1 min-w-0 pr-1 pt-0.5">
                        <div class="flex items-center justify-between gap-2">
                            <h4
                                class="text-xs sm:text-[13px] font-bold tracking-tight leading-none"
                                :class="config.titleColor"
                            >
                                {{ defaultTitle }}
                            </h4>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-[#94A3B8] leading-relaxed mt-1.5 break-words font-medium">
                            {{ message }}
                        </p>
                    </div>

                    <!-- Tombol Tutup Cepat (Close Button) -->
                    <button
                        type="button"
                        class="p-1 -mr-1 -mt-1 rounded-lg text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-[#F1F5F9] hover:bg-slate-100 dark:hover:bg-[#1E293B] transition-colors cursor-pointer shrink-0"
                        title="Tutup Notifikasi"
                        aria-label="Tutup Notifikasi"
                        @click="close"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <!-- Garis Indikator Waktu Otomatis (Progress Line Menyusut) -->
                    <div
                        v-if="autoClose"
                        class="absolute bottom-0 left-0 right-0 h-[2.5px] bg-slate-100 dark:bg-[#1E293B] overflow-hidden"
                    >
                        <div
                            class="h-full transition-all ease-linear"
                            :class="[config.progress, { 'opacity-70': isPaused }]"
                            :style="{
                                width: `${(remainingTime / duration) * 100}%`,
                                transitionDuration: isPaused ? '0ms' : '100ms',
                            }"
                        />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
