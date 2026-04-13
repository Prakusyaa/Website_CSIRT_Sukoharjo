<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Users, Shield } from 'lucide-vue-next';

defineProps<{
    active: 'accounts' | 'roles'
}>();
</script>

<template>
    <nav class="flex items-center gap-2 bg-muted/50 p-1 rounded-lg w-max border">
        <Link 
            href="/admin/users" 
            :class="[
                'inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-md transition-all',
                active === 'accounts' 
                    ? 'bg-background text-foreground shadow-sm' 
                    : 'text-muted-foreground hover:bg-background/50 hover:text-foreground'
            ]"
        >
            <Users class="h-4 w-4" />
            Accounts
        </Link>
        <Link 
            href="/admin/roles" 
            :class="[
                'inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-md transition-all',
                active === 'roles' 
                    ? 'bg-background text-foreground shadow-sm' 
                    : 'text-muted-foreground hover:bg-background/50 hover:text-foreground'
            ]"
        >
            <Shield class="h-4 w-4" />
            Roles
        </Link>
    </nav>
</template>
