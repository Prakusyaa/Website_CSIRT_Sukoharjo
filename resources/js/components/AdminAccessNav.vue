<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    active: 'accounts' | 'roles';
}>();
</script>

<template>
    <nav
        class="inline-flex gap-1 rounded-lg border bg-muted/40 p-1 text-sm font-medium shadow-sm dark:border-gray-800"
        aria-label="Admin access management"
    >
        <template v-if="active === 'accounts'">
            <span class="rounded-md bg-background px-3 py-1.5 text-foreground shadow-sm">Accounts</span>
        </template>
        <Link
            v-else
            href="/admin/users"
            class="rounded-md px-3 py-1.5 text-muted-foreground transition-colors hover:text-foreground"
        >
            Accounts
        </Link>
        <template v-if="active === 'roles'">
            <span class="rounded-md bg-background px-3 py-1.5 text-foreground shadow-sm">Roles</span>
        </template>
        <Link
            v-else
            href="/admin/roles"
            class="rounded-md px-3 py-1.5 text-muted-foreground transition-colors hover:text-foreground"
        >
            Roles
        </Link>
    </nav>
</template>
