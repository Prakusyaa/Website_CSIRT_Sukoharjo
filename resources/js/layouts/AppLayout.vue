<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
const page = usePage();
</script>

<template>
    <AppLayout v-if="page.props.auth.user" :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
    <template v-else>
        <slot />
    </template>
</template>
