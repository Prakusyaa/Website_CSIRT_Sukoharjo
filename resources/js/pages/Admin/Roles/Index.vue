<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Shield, Plus, Pencil, Trash2, Lock, CheckCircle2, ShieldAlert } from 'lucide-vue-next';
import { ref } from 'vue';

interface Role {
    id: number;
    name: string;
    level: number;
    users_count: number;
}

interface PaginatedData {
    data: Role[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number;
    to: number;
    total: number;
}

const props = defineProps<{
    roles: PaginatedData;
    protectedLevels: number[];
}>();

const breadcrumbs = [
    { title: 'Admin', href: '/dashboard' },
    { title: 'Role Management', href: '/admin/roles' },
];

const isProtected = (level: number) => props.protectedLevels.includes(level);

const levelBadge = (level: number) => {
    if (level >= 100) return 'bg-indigo-100 text-indigo-800 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800';
    if (level >= 50)  return 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800';
    return 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700';
};

// Delete confirmation
const confirmingDelete = ref<number | null>(null);
const deleteForm = useForm({});

const confirmDelete = (id: number) => { confirmingDelete.value = id; };
const cancelDelete  = () => { confirmingDelete.value = null; };
const performDelete = (role: Role) => {
    deleteForm.delete(`/admin/roles/${role.id}`, {
        onFinish: () => { confirmingDelete.value = null; },
    });
};
</script>

<template>
    <Head title="Role Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6 mx-auto w-full max-w-4xl">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <Shield class="h-6 w-6 text-primary" /> Role Management
                    </h2>
                    <p class="text-sm text-muted-foreground mt-1">
                        Manage access roles and their permission levels. Core system roles are protected.
                    </p>
                </div>
                <Link
                    href="/admin/roles/create"
                    class="inline-flex items-center gap-2 rounded-md bg-primary text-primary-foreground text-sm font-medium shadow hover:bg-primary/90 h-9 px-4"
                >
                    <Plus class="h-4 w-4" /> New Role
                </Link>
            </div>

            <!-- Flash messages -->
            <div v-if="$page.props.flash?.success"
                class="flex items-center gap-3 rounded-md border border-emerald-200 bg-emerald-50 p-4 dark:bg-emerald-950/40 dark:border-emerald-900">
                <CheckCircle2 class="h-5 w-5 text-emerald-500 shrink-0" />
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ $page.props.flash.success }}</p>
            </div>
            <div v-if="$page.props.errors?.error"
                class="flex items-center gap-3 rounded-md border border-rose-200 bg-rose-50 p-4 dark:bg-rose-950/40 dark:border-rose-900">
                <ShieldAlert class="h-5 w-5 text-rose-500 shrink-0" />
                <p class="text-sm font-medium text-rose-800 dark:text-rose-200">{{ $page.props.errors.error }}</p>
            </div>

            <!-- Protected levels explainer -->
            <div class="rounded-lg border border-amber-200 bg-amber-50/50 p-4 dark:bg-amber-900/10 dark:border-amber-800">
                <div class="flex gap-2 text-sm text-amber-800 dark:text-amber-300">
                    <Lock class="h-4 w-4 mt-0.5 shrink-0" />
                    <div>
                        <span class="font-semibold">Core system roles (levels {{ protectedLevels.join(', ') }}) are locked.</span>
                        They are used directly by authorization policies and cannot be renamed, leveled, or deleted.
                        Only custom roles (levels 1–99, excluding reserved) can be freely managed.
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden dark:border-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-muted-foreground bg-muted/50 border-b dark:bg-gray-900/50 dark:border-gray-800">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Role Name</th>
                                <th class="px-6 py-4 font-semibold">Level</th>
                                <th class="px-6 py-4 font-semibold text-center">Assigned Users</th>
                                <th class="px-6 py-4 font-semibold text-center">Protected</th>
                                <th class="px-6 py-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border-b dark:divide-gray-800 dark:border-gray-800">
                            <tr v-for="role in roles.data" :key="role.id"
                                class="bg-card hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors align-top">

                                <!-- Name -->
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-foreground">{{ role.name }}</div>
                                    <div v-if="isProtected(role.level)" class="text-[11px] text-muted-foreground mt-0.5 flex items-center gap-1">
                                        <Lock class="h-3 w-3" /> System role — read-only
                                    </div>
                                </td>

                                <!-- Level badge -->
                                <td class="px-6 py-4">
                                    <span :class="['inline-flex items-center border rounded-full px-2.5 py-0.5 text-xs font-bold font-mono', levelBadge(role.level)]">
                                        {{ role.level }}
                                    </span>
                                </td>

                                <!-- Users count -->
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-medium">{{ role.users_count }}</span>
                                    <span class="text-xs text-muted-foreground ml-1">{{ role.users_count === 1 ? 'user' : 'users' }}</span>
                                </td>

                                <!-- Protected indicator -->
                                <td class="px-6 py-4 text-center">
                                    <Lock v-if="isProtected(role.level)" class="h-4 w-4 text-amber-500 mx-auto" title="Protected system role" />
                                    <span v-else class="text-muted-foreground text-xs">—</span>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div v-if="!isProtected(role.level)" class="flex items-center justify-end gap-2">
                                        <Link :href="`/admin/roles/${role.id}/edit`"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                                            title="Edit role">
                                            <Pencil class="h-4 w-4" />
                                        </Link>

                                        <!-- Inline delete confirmation -->
                                        <button v-if="confirmingDelete !== role.id"
                                            type="button"
                                            @click="confirmDelete(role.id)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-muted-foreground hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-colors dark:hover:bg-rose-900/20"
                                            title="Delete role">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                        <div v-else class="flex items-center gap-1.5 text-xs">
                                            <span class="text-rose-600 font-medium">Confirm?</span>
                                            <button @click="performDelete(role)"
                                                class="rounded px-2 py-1 bg-rose-600 text-white font-medium hover:bg-rose-700 transition-colors">
                                                Yes
                                            </button>
                                            <button @click="cancelDelete"
                                                class="rounded px-2 py-1 border hover:bg-muted transition-colors">
                                                No
                                            </button>
                                        </div>
                                    </div>
                                    <span v-else class="text-xs text-muted-foreground italic">Protected</span>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="roles.data.length > 0"
                    class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 border-t bg-muted/20 dark:border-gray-800">
                    <span class="text-sm text-muted-foreground">
                        Showing <span class="font-medium text-foreground">{{ roles.from }}</span>–<span class="font-medium text-foreground">{{ roles.to }}</span>
                        of <span class="font-medium text-foreground">{{ roles.total }}</span> roles
                    </span>
                    <nav class="flex items-center gap-1">
                        <template v-for="(link, i) in roles.links" :key="i">
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
    </AppLayout>
</template>
