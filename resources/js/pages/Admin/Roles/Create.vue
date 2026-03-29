<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import AdminAccessNav from '@/components/AdminAccessNav.vue';
import { Shield, AlertCircle, Loader2, Info } from 'lucide-vue-next';

defineProps<{ protectedLevels: number[] }>();

const breadcrumbs = [
    { title: 'Admin', href: '/dashboard' },
    { title: 'Role Management', href: '/admin/roles' },
    { title: 'New Role', href: '#' },
];

const form = useForm({
    name:  '',
    level: '' as number | '',
});

const submit = () => form.post('/admin/roles');
</script>

<template>
    <Head title="Create Role" />


        <div class="flex flex-1 flex-col gap-6 p-6 mx-auto w-full max-w-xl">

            <div class="space-y-3">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <Shield class="h-6 w-6 text-primary" /> Create Custom Role
                    </h2>
                    <p class="text-sm text-muted-foreground mt-1">
                        Define a new access role. System levels ({{ protectedLevels.join(', ') }}) are reserved.
                    </p>
                </div>
                <AdminAccessNav active="roles" />
            </div>

            <!-- Level guide -->
            <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-4 dark:bg-blue-900/10 dark:border-blue-800 text-xs text-blue-800 dark:text-blue-300 space-y-1">
                <p class="font-semibold flex items-center gap-1.5"><Info class="h-3.5 w-3.5" /> Level Guidelines</p>
                <ul class="list-inside list-disc space-y-0.5 ml-5">
                    <li>Level <strong>10</strong>: Reserved — Staff</li>
                    <li>Level <strong>11–49</strong>: Between Staff and CSIRT</li>
                    <li>Level <strong>50</strong>: Reserved — CSIRT</li>
                    <li>Level <strong>51–99</strong>: Elevated but below Admin</li>
                    <li>Level <strong>100</strong>: Reserved — Admin (not assignable to custom roles)</li>
                </ul>
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
                            placeholder="e.g. Tier 2 Analyst"
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
                            placeholder="11–99 (excluding {{ protectedLevels.join(', ') }})"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-mono focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p class="text-xs text-muted-foreground">
                            Higher numbers grant broader system access. Must be unique and not a reserved level.
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
                            Create Role
                        </button>
                    </div>

                </form>
            </div>

        </div>

</template>
