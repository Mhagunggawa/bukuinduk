<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue?: string | number | null;
    label?: string;
    error?: string;
    hint?: string;
    id?: string;
    type?: string;
    placeholder?: string;
    disabled?: boolean;
    required?: boolean;
    autocomplete?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    label: undefined,
    error: undefined,
    hint: undefined,
    id: undefined,
    type: 'text',
    placeholder: undefined,
    disabled: false,
    required: false,
    autocomplete: undefined,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const inputId = computed(() => props.id || (props.label ? `input-${props.label.toLowerCase().replace(/\s+/g, '-')}` : undefined));

const handleInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <div class="space-y-1.5 text-left">
        <label
            v-if="label"
            :for="inputId"
            class="block text-xs font-semibold text-[#0F3A5F] dark:text-[#E2E8F0]"
        >
            {{ label }}
            <span v-if="required" class="text-rose-500 ml-0.5">*</span>
        </label>

        <div class="relative flex items-center">
            <div v-if="$slots.prefix" class="absolute left-3 text-slate-400 pointer-events-none flex items-center">
                <slot name="prefix" />
            </div>

            <input
                :id="inputId"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :autocomplete="autocomplete"
                @input="handleInput"
                :class="[
                    'w-full bg-white dark:bg-[#0B1120] text-xs sm:text-sm text-[#0F3A5F] dark:text-[#F1F5F9] rounded-[8px] border transition-colors placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#BFE3FA] dark:focus:ring-[#1E3A5F] focus:border-[#2B7CC2] dark:focus:border-[#38BDF8] disabled:bg-slate-50 dark:disabled:bg-slate-900 disabled:text-slate-400 disabled:cursor-not-allowed',
                    $slots.prefix ? 'pl-9' : 'pl-3.5',
                    $slots.suffix ? 'pr-9' : 'pr-3.5',
                    'py-2',
                    error ? 'border-rose-300 dark:border-rose-500 focus:border-rose-500 focus:ring-rose-100 dark:focus:ring-rose-950/50' : 'border-[#E2E8F0] dark:border-[#223354]',
                ]"
                v-bind="$attrs"
            />

            <div v-if="$slots.suffix" class="absolute right-3 text-slate-400 flex items-center">
                <slot name="suffix" />
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
