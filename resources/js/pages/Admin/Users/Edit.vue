<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Users, AlertCircle, Loader2, ShieldAlert } from 'lucide-vue-next';

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
    password:  '',      // blank = keep existing
    role_id:   props.user.role_id,
    is_active: props.user.is_active,
});

const submit = () => form.put(`/admin/users/${props.user.id}`);
</script>

<template>
    <Head :title="`Edit: ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6 mx-auto w-full max-w-2xl">

            <!-- Header -->
            <div>
                <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                    <Users class="h-6 w-6 text-primary" /> Edit Account
                </h2>
                <p class="text-sm text-muted-foreground mt-1">
                    Modifying profile for <span class="font-semibold">{{ user.name }}</span>.
                </p>
            </div>

            <!-- Role demotion error -->
            <div v-if="form.errors.role_id && form.errors.role_id.includes('demot')"
                class="flex items-center gap-3 rounded-md border border-rose-200 bg-rose-50 p-4 dark:bg-rose-950/40 dark:border-rose-900">
                <ShieldAlert class="h-5 w-5 text-rose-500 shrink-0" />
                <p class="text-sm font-medium text-rose-800 dark:text-rose-200">{{ form.errors.role_id }}</p>
            </div>

            <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                <div class="px-6 py-5 border-b dark:border-gray-800">
                    <h3 class="font-semibold text-base">Account Details</h3>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-5">

                    <!-- Name -->
                    <div class="space-y-1.5">
                        <label for="name" class="text-sm font-medium">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="name" v-model="form.name" type="text"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="form.errors.name" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label for="email" class="text-sm font-medium">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="email" v-model="form.email" type="email"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="form.errors.email" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Username -->
                    <div class="space-y-1.5">
                        <label for="username" class="text-sm font-medium">
                            Username <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="username" v-model="form.username" type="text"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-mono focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="form.errors.username" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.username }}
                        </p>
                    </div>

                    <!-- Password reset -->
                    <div class="space-y-1.5">
                        <label for="password" class="text-sm font-medium">
                            Reset Password
                            <span class="text-muted-foreground font-normal">(leave blank to keep current)</span>
                        </label>
                        <input
                            id="password" v-model="form.password" type="password"
                            placeholder="New password…"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="form.errors.password" class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Role assignment -->
                    <div class="space-y-1.5">
                        <label for="role_id" class="text-sm font-medium">
                            Security Role <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="role_id" v-model="form.role_id"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                {{ role.name }} — Level {{ role.level }}
                            </option>
                        </select>
                        <p v-if="form.errors.role_id && !form.errors.role_id.includes('demot')"
                            class="text-[0.8rem] font-medium text-destructive flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.role_id }}
                        </p>
                    </div>

                    <!-- Activate / Deactivate toggle -->
                    <div class="rounded-lg border p-4 dark:border-gray-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium">Account Access</p>
                                <p class="text-xs text-muted-foreground mt-0.5">
                                    Disabled accounts cannot log in to the system.
                                </p>
                            </div>
                            <!-- Toggle switch -->
                            <button
                                type="button"
                                role="switch"
                                :aria-checked="form.is_active"
                                @click="form.is_active = !form.is_active"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                :class="form.is_active ? 'bg-primary' : 'bg-input'"
                            >
                                <span
                                    class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform"
                                    :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"
                                />
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                :class="form.is_active
                                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20'
                                    : 'bg-gray-50 text-gray-500 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20'"
                            >
                                <span class="h-1.5 w-1.5 rounded-full" :class="form.is_active ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                {{ form.is_active ? 'Active — can log in' : 'Disabled — login blocked' }}
                            </span>
                        </div>
                    </div>

                    <!-- Self-edit safety notice -->
                    <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:bg-amber-900/20 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300">
                        ⚠ If editing your own account, you cannot change your role below Admin or disable your own access.
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-2 border-t dark:border-gray-800">
                        <Link
                            href="/admin/users"
                            class="inline-flex h-9 px-4 items-center rounded-md border text-sm font-medium hover:bg-muted transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit" :disabled="form.processing"
                            class="inline-flex h-9 px-4 items-center gap-2 rounded-md bg-primary text-primary-foreground text-sm font-medium shadow hover:bg-primary/90 disabled:opacity-50 transition-colors"
                        >
                            <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Save Changes
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </AppLayout>
</template>
