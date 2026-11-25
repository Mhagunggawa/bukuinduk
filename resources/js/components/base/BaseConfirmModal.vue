<script setup lang="ts">
import { computed, watch, onMounted, onUnmounted } from 'vue';
import {
    AlertTriangle,
    AlertCircle,
    Info,
    Trash2,
    CheckCircle2,
    X,
    FileText,
} from '@lucide/vue';
import BaseButton from '@/components/base/BaseButton.vue';

interface Props {
    open?: boolean;
    isOpen?: boolean;
    title?: string;
    message?: string;
    itemInfo?: string;
    subInfo?: string;
    badgeText?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'warning' | 'primary' | 'success';
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    open: false,
    isOpen: false,
    title: 'Konfirmasi Tindakan',
    message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    itemInfo: undefined,
    subInfo: undefined,
    badgeText: undefined,
    confirmText: 'Konfirmasi',
    cancelText: 'Batal',
    variant: 'danger',
    loading: false,
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
    (e: 'update:open', val: boolean): void;
    (e: 'update:isOpen', val: boolean): void;
}>();

const isVisible = computed(() => Boolean(props.open || props.isOpen));

const close = () => {
    if (props.loading) return;
    emit('close');
    emit('update:open', false);
    emit('update:isOpen', false);
};

const handleConfirm = () => {
    emit('confirm');
};

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isVisible.value && !props.loading) {
        close();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

watch(
    () => isVisible.value,
    (val) => {
        if (val) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
);

const iconBgClasses = computed(() => {
    switch (props.variant) {
        case 'danger':
            return 'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border-red-100 dark:border-red-900/60';
        case 'warning':
            return 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900/60';
        case 'success':
            return 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/60';
        case 'primary':
        default:
            return 'bg-[#EAF4FC] dark:bg-[#1E3A5F]/40 text-[#2B7CC2] dark:text-[#38BDF8] border-[#BFE3FA]/70 dark:border-[#2B7CC2]/40';
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isVisible"
                class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
                @click.self="close"
            >
                <div
                    class="w-full max-w-md bg-white dark:bg-[#131F37] rounded-2xl border border-[#E2E8F0] dark:border-[#223354] shadow-2xl overflow-hidden transform transition-all select-none animate-in fade-in zoom-in-95 duration-150 flex flex-col"
                >
                    <!-- Header Bar Terpadu -->
                    <div class="px-5 sm:px-6 pt-5 pb-4 border-b border-[#F1F5F9] dark:border-[#223354] flex items-center justify-between gap-3 bg-white dark:bg-[#131F37]">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Icon Badge Proporsional -->
                            <div
                                :class="[
                                    'w-10 h-10 rounded-xl border flex items-center justify-center shrink-0 shadow-2xs',
                                    iconBgClasses,
                                ]"
                            >
                                <Trash2 v-if="variant === 'danger'" class="w-4 h-4 text-red-600 dark:text-red-400" />
                                <AlertTriangle v-else-if="variant === 'warning'" class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                <CheckCircle2 v-else-if="variant === 'success'" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                <Info v-else class="w-4 h-4 text-[#2B7CC2] dark:text-[#38BDF8]" />
                            </div>

                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-[#0F3A5F] dark:text-[#F1F5F9] tracking-tight truncate">
                                    {{ title }}
                                </h3>
                                <p v-if="badgeText" class="text-[11px] text-[#64748B] dark:text-[#94A3B8]">
                                    {{ badgeText }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-[#0F3A5F] dark:hover:text-[#F1F5F9] hover:bg-slate-100 dark:hover:bg-[#1E293B] transition-colors cursor-pointer shrink-0"
                            aria-label="Tutup"
                            :disabled="loading"
                            @click="close"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Body Content -->
                    <div class="p-5 sm:p-6 space-y-3.5">
                        <p class="text-xs sm:text-sm text-[#64748B] dark:text-[#94A3B8] leading-relaxed">
                            {{ message }}
                        </p>

                        <!-- Target Info Box: Rapi, Terstruktur & Terpusat -->
                        <div
                            v-if="itemInfo || subInfo"
                            class="p-3.5 bg-[#F8FAFC] dark:bg-[#0E1726] border border-[#E2E8F0] dark:border-[#223354] rounded-xl flex items-start gap-3 text-left"
                        >
                            <div class="w-8 h-8 rounded-lg bg-white dark:bg-[#1E293B] border border-[#E2E8F0] dark:border-[#334155] flex items-center justify-center shrink-0 shadow-2xs mt-0.5">
                                <AlertCircle v-if="variant === 'danger'" class="w-4 h-4 text-red-500 dark:text-red-400" />
                                <FileText v-else class="w-4 h-4 text-[#2B7CC2] dark:text-[#38BDF8]" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p v-if="itemInfo" class="text-xs sm:text-[13px] font-bold text-[#0F3A5F] dark:text-[#F1F5F9] truncate leading-snug">
                                    {{ itemInfo }}
                                </p>
                                <p v-if="subInfo" class="text-[11px] text-[#64748B] dark:text-[#94A3B8] mt-0.5 leading-normal">
                                    {{ subInfo }}
                                </p>
                            </div>
                        </div>

                        <slot />
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-5 sm:px-6 py-3.5 bg-[#F8FAFC] dark:bg-[#0E1726] border-t border-[#E2E8F0] dark:border-[#223354] flex items-center justify-end gap-2.5">
                        <BaseButton
                            type="button"
                            variant="secondary"
                            size="sm"
                            :disabled="loading"
                            @click="close"
                        >
                            {{ cancelText }}
                        </BaseButton>
                        <BaseButton
                            type="button"
                            :variant="variant === 'danger' ? 'danger' : 'primary'"
                            size="sm"
                            :loading="loading"
                            @click="handleConfirm"
                        >
                            {{ confirmText }}
                        </BaseButton>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
