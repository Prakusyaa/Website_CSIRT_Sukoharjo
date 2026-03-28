<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Users, AlertCircle, Loader2 } from 'lucide-vue-next';

interface Role {
    id: number;
    name: string;
    level: number;
}

interface UserData {
    id: number;
    name: string;
    username: string;
    email: string;
    role_id: number;
    is_active: boolean;
}

const props = defineProps<{ user: UserData; roles: Role[] }>();

const breadcrumbs = [
    { title: 'Admin', href: '/dashboard' },
    { title: 'User Management', href: '/admin/users' },
    { title: 'Edit Account', href: '#' },
];

const form = useForm({
    name:      props.user.name,
    email:     props.user.email,
    username:  props.user.username,
    password:  '',          // blank = don't change
    role_id:   props.user.role_id,
    is_active: props.user.is_active,
});

const submit = () =>
    form.put(`/admin/users/${props.user.id}`);
</script>

<template>
    <Head :title="`Edit: ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6 mx-auto w-full max-w-2xl">

            <div>
                <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                    <Users class="h-6 w-6 text-primary" /> Edit Account
                </h2>
                <p class="text-sm text-muted-foreground mt-1">Modifying profile for <span class="font-semibold">{{ user.name }}</span>.</p>
            </div>

            <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                <div class="px-6 py-5 border-b dark:border-gray-800">
                    <h3 class="font-semibold text-base">Account Details</h3>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-5">

                    <!-- Name -->
                    <div class="space-y-1.5">
                        <label for="name" class="text-sm font-medium">Full Name <span class="text-rose-500">*</span></label>
                        <input id="name" v-model="form.name" type="text"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                        <p v-if="form.errors.name" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label for="email" class="text-sm font-medium">Email Address <span class="text-rose-500">*</span></label>
                        <input id="email" v-model="form.email" type="email"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                        <p v-if="form.errors.email" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Username -->
                    <div class="space-y-1.5">
                        <label for="username" class="text-sm font-medium">Username <span class="text-rose-500">*</span></label>
                        <input id="username" v-model="form.username" type="text"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-mono focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                        <p v-if="form.errors.username" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.username }}
                        </p>
                    </div>

                    <!-- Password reset -->
                    <div class="space-y-1.5">
                        <label for="password" class="text-sm font-medium">Reset Password
                            <span class="text-muted-foreground font-normal">(leave blank to keep current)</span>
                        </label>
                        <input id="password" v-model="form.password" type="password" placeholder="New password…"
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
                            <option v-for="role in roles" :key="role.id" :value="role.id">
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
                            <span class="text-muted-foreground font-normal">(uncheck to disable login access)</span>
                        </label>
                    </div>

                    <!-- Danger notice for self-edit -->
                    <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:bg-amber-900/20 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300">
                        ⚠ You cannot demote or deactivate your own Admin account for safety reasons.
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-2 border-t dark:border-gray-800">
                        <Link href="/admin/users" class="inline-flex h-9 px-4 items-center rounded-md border text-sm font-medium hover:bg-muted transition-colors">
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
    </AppLayout>
</template>
