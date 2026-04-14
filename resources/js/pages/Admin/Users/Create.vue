    <script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import AdminAccessNav from '@/components/AdminAccessNav.vue';
import { Users, AlertCircle, Loader2 } from 'lucide-vue-next';

interface Role {
    id: number;
    name: string;
    level: number;
}

const props = defineProps<{
    roles: Role[];
}>();

const breadcrumbs = [
    { title: 'Admin', href: '/dashboard' },
    { title: 'User Management', href: '/admin/users' },
    { title: 'New Account', href: '/admin/users/create' },
];

const form = useForm({
    name: '',
    email: '',
    username: '',
    password: '',
    role_id: '' as number | '',
    is_active: true,
});

const submit = () => form.post('/admin/users');
</script>

<template>
    <Head title="Create User Account" />


        <div class="flex flex-1 flex-col gap-6 p-6 mx-auto w-full max-w-2xl">

            <div class="space-y-3">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <Users class="h-6 w-6 text-primary" /> Provision New Account
                    </h2>
                    <p class="text-sm text-muted-foreground mt-1">Create a new CSIRT system user and assign their access role.</p>
                </div>
                <AdminAccessNav active="accounts" />
            </div>

            <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                <div class="px-6 py-5 border-b dark:border-gray-800">
                    <h3 class="font-semibold text-base">Account Details</h3>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-5">

                    <!-- Name -->
                    <div class="space-y-1.5">
                        <label for="name" class="text-sm font-medium">Full Name <span class="text-rose-500">*</span></label>
                        <input id="name" v-model="form.name" type="text" placeholder="Jane Doe"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                        <p v-if="form.errors.name" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label for="email" class="text-sm font-medium">Email Address <span class="text-rose-500">*</span></label>
                        <input id="email" v-model="form.email" type="email" placeholder="jane@csirt.internal"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                        <p v-if="form.errors.email" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Username -->
                    <div class="space-y-1.5">
                        <label for="username" class="text-sm font-medium">Username <span class="text-rose-500">*</span></label>
                        <input id="username" v-model="form.username" type="text" placeholder="jdoe"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-mono focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                        <p v-if="form.errors.username" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.username }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <label for="password" class="text-sm font-medium">Initial Password <span class="text-rose-500">*</span></label>
                        <input id="password" v-model="form.password" type="password" placeholder="Min. 8 characters"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                        <p v-if="form.errors.password" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Role -->
                    <div class="space-y-1.5">
                        <label for="role_id" class="text-sm font-medium">Security Role <span class="text-rose-500">*</span></label>
                        <select id="role_id" v-model="form.role_id"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                            <option value="" disabled>Select a role…</option>
                            <option v-for="role in props.roles" :key="role.id" :value="role.id">
                                {{ role.name }} (Level {{ role.level }})
                            </option>
                        </select>
                        <p v-if="form.errors.role_id" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.role_id }}
                        </p>
                    </div>

                    <!-- Active toggle -->
                    <div class="flex items-center gap-3 pt-1">
                        <input id="is_active" v-model="form.is_active" type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                        <label for="is_active" class="text-sm font-medium cursor-pointer select-none">
                            Account Active
                            <span class="text-muted-foreground font-normal">(uncheck to provision as disabled)</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-2 border-t dark:border-gray-800">
                        <Link href="/admin/users" class="inline-flex h-9 px-4 items-center rounded-md border text-sm font-medium hover:bg-muted transition-colors">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex h-9 px-4 items-center gap-2 rounded-md bg-primary text-primary-foreground text-sm font-medium shadow hover:bg-primary/90 disabled:opacity-50 transition-colors">
                            <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Create Account
                        </button>
                    </div>

                </form>
            </div>

        </div>

</template>
