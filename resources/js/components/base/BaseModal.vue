<script setup lang="ts">
import { watch, onMounted, onUnmounted, computed } from 'vue';
import { X } from '@lucide/vue';

interface Props {
    open?: boolean;
    isOpen?: boolean;
    title?: string;
    description?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl';
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl';
}

const props = withDefaults(defineProps<Props>(), {
    open: false,
    isOpen: false,
    title: undefined,
    description: undefined,
    size: 'md',
    maxWidth: undefined,
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update:open', value: boolean): void;
    (e: 'update:isOpen', value: boolean): void;
}>();

const isVisible = computed(() => Boolean(props.open || props.isOpen));

const close = () => {
    emit('close');
    emit('update:open', false);
    emit('update:isOpen', false);
};

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isVisible.value) {
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

const sizeClasses = computed(() => {
    const s = props.maxWidth || props.size;
    switch (s) {
        case 'sm':
            return 'sm:max-w-sm';
        case 'lg':
            return 'sm:max-w-xl';
        case 'xl':
            return 'sm:max-w-2xl';
        case '2xl':
            return 'sm:max-w-3xl';
        case '3xl':
        case '4xl':
            return 'sm:max-w-4xl';
        case 'md':
        default:
            return 'sm:max-w-lg';
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isVisible"
                class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
                @click.self="close"
            >
                <div
                    :class="[
                        'w-full bg-white dark:bg-[#131F37] rounded-[12px] border border-[#E2E8F0] dark:border-[#223354] shadow-xl transform transition-all flex flex-col max-h-[90vh]',
                        sizeClasses,
                    ]"
                >
                    <!-- Header -->
                    <div
                        v-if="title || $slots.header"
                        class="px-5 sm:px-6 py-4 border-b border-[#E2E8F0] dark:border-[#223354] flex items-center justify-between shrink-0"
                    >
                        <slot name="header">
                            <div>
                                <h3 class="text-base font-bold text-[#0F3A5F] dark:text-[#F1F5F9] tracking-tight">
                                    {{ title }}
                                </h3>
                                <p v-if="description" class="text-xs text-[#64748B] dark:text-[#94A3B8] mt-0.5">
                                    {{ description }}
                                </p>
                            </div>
                        </slot>

                        <button
                            type="button"
                            class="p-1 rounded-lg text-slate-400 hover:text-[#0F3A5F] dark:hover:text-[#F1F5F9] hover:bg-slate-100 dark:hover:bg-[#1E293B] transition-colors cursor-pointer"
                            aria-label="Tutup"
                            @click="close"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-5 sm:px-6 py-5 overflow-y-auto flex-1 text-[#0F3A5F] dark:text-[#E2E8F0]">
                        <slot />
                    </div>

                    <!-- Footer -->
                    <div
                        v-if="$slots.footer"
                        class="px-5 sm:px-6 py-3.5 bg-[#F8FAFC] dark:bg-[#0E1726] border-t border-[#E2E8F0] dark:border-[#223354] rounded-b-[11px] flex items-center justify-end gap-2.5 shrink-0"
                    >
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
