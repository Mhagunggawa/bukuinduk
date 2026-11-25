<script setup lang="ts">
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { LogOut, X } from '@lucide/vue';
import BaseButton from '@/components/base/BaseButton.vue';

interface Props {
    open?: boolean;
    isOpen?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    open: false,
    isOpen: false,
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update:open', val: boolean): void;
    (e: 'update:isOpen', val: boolean): void;
}>();

const isVisible = computed(() => Boolean(props.open || props.isOpen));
const isLoading = ref(false);

const close = () => {
    if (isLoading.value) return;
    emit('close');
    emit('update:open', false);
    emit('update:isOpen', false);
};

const handleLogout = () => {
    isLoading.value = true;
    router.post('/logout', {}, {
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isVisible.value && !isLoading.value) {
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
                class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4"
                @click.self="close"
            >
                <div
                    class="relative w-full max-w-sm bg-white dark:bg-[#131F37] rounded-[14px] border border-[#E2E8F0] dark:border-[#223354] shadow-xl overflow-hidden transform transition-all select-none"
                >
                    <!-- Close button -->
                    <button
                        type="button"
                        class="absolute top-3.5 right-3.5 p-1 rounded-lg text-slate-400 hover:text-[#0F3A5F] dark:hover:text-[#F1F5F9] hover:bg-slate-100 dark:hover:bg-[#1E293B] transition-colors cursor-pointer"
                        aria-label="Tutup"
                        :disabled="isLoading"
                        @click="close"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <!-- Body Content -->
                    <div class="p-6 text-center">
                        <div
                            class="mx-auto w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-3.5"
                        >
                            <LogOut class="w-5 h-5 ml-0.5" />
                        </div>

                        <h3 class="text-base font-bold text-[#0F3A5F] dark:text-[#F1F5F9]">
                            Keluar dari Sistem?
                        </h3>
                        <p class="text-xs text-[#64748B] dark:text-[#94A3B8] mt-1 leading-relaxed">
                            Sesi aktif Anda saat ini akan diakhiri.
                        </p>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-6 py-3.5 bg-[#F8FAFC] dark:bg-[#0E1726] border-t border-[#E2E8F0] dark:border-[#223354] grid grid-cols-2 gap-2.5">
                        <BaseButton
                            type="button"
                            variant="secondary"
                            size="sm"
                            :disabled="isLoading"
                            class="w-full justify-center"
                            @click="close"
                        >
                            Batal
                        </BaseButton>
                        <BaseButton
                            type="button"
                            variant="danger"
                            size="sm"
                            :loading="isLoading"
                            class="w-full justify-center"
                            @click="handleLogout"
                        >
                            Ya, Keluar
                        </BaseButton>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
