<script setup lang="ts">
import { computed } from 'vue';

interface Column {
    key: string;
    label: string;
    align?: 'left' | 'center' | 'right';
    width?: string;
}

interface Props {
    columns?: Column[];
    isEmpty?: boolean;
    itemCount?: number;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    columns: () => [],
    isEmpty: false,
    itemCount: undefined,
    loading: false,
});

const isTableEmpty = computed(() => {
    if (props.itemCount !== undefined) {
        return props.itemCount === 0;
    }
    return props.isEmpty;
});
</script>

<template>
    <div class="w-full bg-white dark:bg-[#131F37] rounded-[10px] border border-[#E2E8F0] dark:border-[#223354] shadow-[0_1px_3px_0_rgba(15,58,95,0.04)] dark:shadow-none overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F8FAFC] dark:bg-[#0E1726] border-b border-[#E2E8F0] dark:border-[#223354]">
                    <slot name="header">
                        <tr>
                            <th
                                v-for="col in columns"
                                :key="col.key"
                                :style="col.width ? { width: col.width } : {}"
                                :class="[
                                    'px-4 py-3 text-[11px] font-bold text-[#64748B] dark:text-[#94A3B8] uppercase tracking-wider',
                                    col.align === 'center' ? 'text-center' : col.align === 'right' ? 'text-right' : 'text-left',
                                ]"
                            >
                                {{ col.label }}
                            </th>
                        </tr>
                    </slot>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] dark:divide-[#223354] text-xs sm:text-sm text-[#0F3A5F] dark:text-[#E2E8F0]">
                    <slot name="body">
                        <slot />
                    </slot>
                </tbody>
            </table>
        </div>

        <div v-if="isTableEmpty" class="py-12 px-4 text-center">
            <slot name="empty" />
        </div>
    </div>
</template>
