<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import AdminAccessNav from '@/components/AdminAccessNav.vue';
import { Shield, AlertCircle, Loader2, Info } from 'lucide-vue-next';

interface RoleData {
    id: number;
    name: string;
    level: number;
}

const props = defineProps<{ role: RoleData; protectedLevels: number[] }>();

const breadcrumbs = [
    { title: 'Admin', href: '/dashboard' },
    { title: 'Role Management', href: '/admin/roles' },
    { title: 'Edit Role', href: '#' },
];

const form = useForm({
    name:  props.role.name,
    level: props.role.level,
});

const submit = () => form.put(`/admin/roles/${props.role.id}`);
</script>

<template>
    <Head :title="`Edit Role: ${role.name}`" />


        <div class="flex flex-1 flex-col gap-6 p-6 mx-auto w-full max-w-xl">

            <div class="space-y-3">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <Shield class="h-6 w-6 text-primary" /> Edit Role
                    </h2>
                    <p class="text-sm text-muted-foreground mt-1">
                        Editing <span class="font-semibold">{{ role.name }}</span> — current level
                        <code class="bg-muted px-1.5 py-0.5 rounded font-mono text-xs">{{ role.level }}</code>.
                    </p>
                </div>
                <AdminAccessNav active="roles" />
            </div>

            <!-- Warning about level changes -->
            <div class="rounded-lg border border-amber-200 bg-amber-50/50 p-4 dark:bg-amber-900/10 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 space-y-1">
                <p class="font-semibold flex items-center gap-1.5"><Info class="h-3.5 w-3.5" /> Level Change Warning</p>
                <p>Changing a role's level affects the permissions of <strong>all users</strong> currently assigned to it.
                   Reserved levels ({{ protectedLevels.join(', ') }}) cannot be used.</p>
            </div>

            <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                <div class="px-6 py-5 border-b dark:border-gray-800">
                    <h3 class="font-semibold text-base">Role Details</h3>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-5">

                    <!-- Name -->
                    <div class="space-y-1.5">
                        <label for="name" class="text-sm font-medium">
                            Role Name <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="name" v-model="form.name" type="text"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="form.errors.name" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Level -->
                    <div class="space-y-1.5">
                        <label for="level" class="text-sm font-medium">
                            Permission Level <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="level" v-model.number="form.level" type="number"
                            min="11" max="99"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-mono focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p class="text-xs text-muted-foreground">
                            Must be 11–99, unique, and not a reserved level ({{ protectedLevels.join(', ') }}).
                        </p>
                        <p v-if="form.errors.level" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.level }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-2 border-t dark:border-gray-800">
                        <Link href="/admin/roles"
                            class="inline-flex h-9 px-4 items-center rounded-md border text-sm font-medium hover:bg-muted transition-colors">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex h-9 px-4 items-center gap-2 rounded-md bg-primary text-primary-foreground text-sm font-medium shadow hover:bg-primary/90 disabled:opacity-50 transition-colors">
                            <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Save Changes
                        </button>
                    </div>

                </form>
            </div>

        </div>

</template>
