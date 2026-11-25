<script setup lang="ts">
import { computed } from 'vue';

interface Option {
    value: string | number;
    label: string;
}

interface Props {
    modelValue?: string | number | null;
    label?: string;
    options?: Option[];
    placeholder?: string;
    error?: string;
    hint?: string;
    disabled?: boolean;
    required?: boolean;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    label: undefined,
    options: () => [],
    placeholder: undefined,
    error: undefined,
    hint: undefined,
    disabled: false,
    required: false,
    id: undefined,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const selectId = computed(() => props.id || (props.label ? `select-${props.label.toLowerCase().replace(/\s+/g, '-')}` : undefined));

const handleChange = (e: Event) => {
    const target = e.target as HTMLSelectElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <div class="space-y-1.5 text-left">
        <label
            v-if="label"
            :for="selectId"
            class="block text-xs font-semibold text-[#0F3A5F] dark:text-[#E2E8F0]"
        >
            {{ label }}
            <span v-if="required" class="text-rose-500 ml-0.5">*</span>
        </label>

        <div class="relative">
            <select
                :id="selectId"
                :value="modelValue"
                :disabled="disabled"
                :required="required"
                @change="handleChange"
                :class="[
                    'w-full bg-white dark:bg-[#0B1120] text-xs sm:text-sm text-[#0F3A5F] dark:text-[#F1F5F9] rounded-[8px] border py-2 pl-3.5 pr-9 truncate appearance-none transition-colors focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] dark:focus:ring-[#1E3A5F] focus:border-[#2B7CC2] dark:focus:border-[#38BDF8] disabled:bg-slate-50 dark:disabled:bg-slate-900 disabled:text-slate-400 disabled:cursor-not-allowed cursor-pointer',
                    error ? 'border-rose-300 dark:border-rose-500 focus:border-rose-500 focus:ring-rose-100 dark:focus:ring-rose-950/50' : 'border-[#E2E8F0] dark:border-[#223354]',
                ]"
                v-bind="$attrs"
            >
                <option v-if="placeholder" value="" disabled selected>
                    {{ placeholder }}
                </option>
                <option
                    v-for="opt in options"
                    :key="opt.value"
                    :value="opt.value"
                >
                    {{ opt.label }}
                </option>
                <slot />
            </select>

            <!-- Custom chevron indicator -->
            <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                    <path d="M7 8l3 3 3-3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>

        <p v-if="error" class="text-[11px] font-medium text-rose-600 mt-1">
            {{ error }}
        </p>
        <p v-else-if="hint" class="text-[11px] text-slate-400 mt-1">
            {{ hint }}
        </p>
    </div>
</template>
