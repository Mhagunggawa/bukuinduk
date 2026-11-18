<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

interface Breadcrumb {
    title: string;
    href?: string;
}

defineProps<{
    breadcrumbs?: Breadcrumb[];
}>();
</script>

<template>
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AuthenticatedLayout>
</template>
