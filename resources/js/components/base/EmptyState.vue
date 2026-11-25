<script setup lang="ts">
import { Inbox } from '@lucide/vue';
import type { Component } from 'vue';

interface Props {
    title?: string;
    description?: string;
    icon?: Component;
}

withDefaults(defineProps<Props>(), {
    title: 'Tidak ada data ditemukan',
    description: 'Belum ada data yang tersedia untuk ditampilkan saat ini.',
    icon: undefined,
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-10 px-4 text-center max-w-sm mx-auto">
        <div class="w-12 h-12 rounded-[10px] bg-[#EAF4FC] text-[#2B7CC2] flex items-center justify-center mb-3 border border-[#BFE3FA]/60">
            <component :is="icon || Inbox" class="w-6 h-6 stroke-[1.5]" />
        </div>
        <h4 class="text-sm font-bold text-[#0F3A5F] tracking-tight">
            {{ title }}
        </h4>
        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">
            {{ description }}
        </p>
        <div v-if="$slots.action" class="mt-4">
            <slot name="action" />
        </div>
    </div>
</template>
