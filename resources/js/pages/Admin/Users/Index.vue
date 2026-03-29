<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import AdminAccessNav from '@/components/AdminAccessNav.vue';
import { Users, Plus, Shield, Pencil, CheckCircle2, ShieldAlert } from 'lucide-vue-next';

interface Role {
    id: number;
    name: string;
    level: number;
}

interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    is_active: boolean;
    role: Role;
    created_at: string;
}

interface PaginatedData {
    data: User[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number;
    to: number;
    total: number;
}

defineProps<{ users: PaginatedData }>();

const breadcrumbs = [
    { title: 'Admin', href: '/dashboard' },
    { title: 'User Management', href: '/admin/users' },
];

const formatDate = (d: string) =>
    new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(d));

const roleClass = (level: number) =>
    level >= 100
        ? 'bg-indigo-100 text-indigo-800 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800'
        : level >= 50
            ? 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800'
            : 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700';
</script>

<template>
    <Head title="User Management" />


        <div class="flex flex-1 flex-col gap-6 p-6 mx-auto w-full max-w-6xl">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                            <Users class="h-6 w-6 text-primary" /> User Management
                        </h2>
                        <p class="text-sm text-muted-foreground mt-1">Provision accounts, assign roles, and control system access.</p>
                    </div>
                    <AdminAccessNav active="accounts" />
                </div>
                <Link
                    href="/admin/users/create"
                    class="inline-flex shrink-0 items-center gap-2 rounded-md bg-primary text-primary-foreground text-sm font-medium shadow hover:bg-primary/90 h-9 px-4"
                >
                    <Plus class="h-4 w-4" /> New Account
                </Link>
            </div>

            <!-- Flash messages -->
            <div v-if="$page.props.flash?.success" class="flex items-center gap-3 rounded-md border border-emerald-200 bg-emerald-50 p-4 dark:bg-emerald-950/40 dark:border-emerald-900">
                <CheckCircle2 class="h-5 w-5 text-emerald-500 shrink-0" />
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ $page.props.flash.success }}</p>
            </div>
            <div v-if="$page.props.errors?.error" class="flex items-center gap-3 rounded-md border border-rose-200 bg-rose-50 p-4 dark:bg-rose-950/40 dark:border-rose-900">
                <ShieldAlert class="h-5 w-5 text-rose-500 shrink-0" />
                <p class="text-sm font-medium text-rose-800 dark:text-rose-200">{{ $page.props.errors.error }}</p>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden dark:border-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-muted-foreground bg-muted/50 border-b dark:bg-gray-900/50 dark:border-gray-800">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Account</th>
                                <th class="px-6 py-4 font-semibold">Username</th>
                                <th class="px-6 py-4 font-semibold">Role</th>
                                <th class="px-6 py-4 font-semibold text-center">Status</th>
                                <th class="px-6 py-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border-b dark:divide-gray-800 dark:border-gray-800">
                            <tr v-for="user in users.data" :key="user.id"
                                class="bg-card hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">

                                <!-- Account Details -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 shrink-0 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-foreground">{{ user.name }}</div>
                                            <div class="text-xs text-muted-foreground">{{ user.email }}</div>
                                            <div class="text-[10px] text-muted-foreground/60 mt-0.5">Joined {{ formatDate(user.created_at) }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 font-mono text-xs text-muted-foreground">@{{ user.username }}</td>

                                <!-- Role badge -->
                                <td class="px-6 py-4">
                                    <span :class="['inline-flex items-center gap-1.5 border rounded-full px-2.5 py-0.5 text-xs font-semibold', roleClass(user.role.level)]">
                                        <Shield class="h-3 w-3" />
                                        {{ user.role.name }}
                                    </span>
                                </td>

                                <!-- Active status -->
                                <td class="px-6 py-4 text-center">
                                    <span v-if="user.is_active"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                    <span v-else
                                        class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-500 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Disabled
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="`/admin/users/${user.id}/edit`"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                                        title="Edit user"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.data.length > 0"
                    class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 border-t bg-muted/20 dark:border-gray-800">
                    <span class="text-sm text-muted-foreground">
                        Showing <span class="font-medium text-foreground">{{ users.from }}</span>–<span class="font-medium text-foreground">{{ users.to }}</span>
                        of <span class="font-medium text-foreground">{{ users.total }}</span> accounts
                    </span>
                    <nav class="flex items-center gap-1">
                        <template v-for="(link, i) in users.links" :key="i">
                            <Link v-if="link.url" :href="link.url"
                                class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2.5 text-sm transition-colors shadow-sm dark:border-gray-700"
                                :class="link.active ? 'bg-primary text-primary-foreground font-semibold border-primary' : 'bg-background text-muted-foreground hover:bg-muted'"
                                v-html="link.label" />
                            <span v-else
                                class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2.5 text-sm text-muted-foreground opacity-40 cursor-not-allowed bg-background dark:border-gray-700"
                                v-html="link.label" />
                        </template>
                    </nav>
                </div>
            </div>

        </div>

</template>
