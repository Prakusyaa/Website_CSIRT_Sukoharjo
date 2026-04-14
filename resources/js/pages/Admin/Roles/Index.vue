<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import AdminAccessNav from '@/components/AdminAccessNav.vue';
import { Shield, Plus, Pencil, Trash2, Lock, CheckCircle2, ShieldAlert } from 'lucide-vue-next';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import ConfirmModal from '@/components/ui/ConfirmModal.vue';

interface Role {
    id: number;
    name: string;
    level: number;
    users_count: number;
    created_at: string;
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

const isProtected = (level: number) => props.protectedLevels.includes(level);

const levelBadge = (level: number) => {
    if (level >= 100) return 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800';
    if (level >= 50)  return 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800';
    return 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700';
};

const levelLabel = (level: number) => {
    if (level >= 100) return 'Administrator';
    if (level >= 50)  return 'CSIRT';
    return 'Staff';
};

// Modal logic
const isModalOpen = ref(false);
const modalMode = ref<'create' | 'edit'>('create');
const editingRoleId = ref<number | null>(null);

const form = useForm({
    name: '',
    level: 11,
});

const openCreateModal = () => {
    modalMode.value = 'create';
    editingRoleId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (role: Role) => {
    if (isProtected(role.level)) return;
    modalMode.value = 'edit';
    editingRoleId.value = role.id;
    form.name = role.name;
    form.level = role.level;
    form.clearErrors();
    isModalOpen.value = true;
};

const submitForm = () => {
    if (modalMode.value === 'create') {
        form.post('/admin/roles', {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.put(`/admin/roles/${editingRoleId.value}`, {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
};

// Delete confirmation
const confirmingDeleteRole = ref<Role | null>(null);
const deleteForm = useForm({});

const confirmDelete = (role: Role) => { confirmingDeleteRole.value = role; };
const executeDeleteRole = () => {
    if (!confirmingDeleteRole.value) return;
    deleteForm.delete(`/admin/roles/${confirmingDeleteRole.value.id}`, {
        onFinish: () => { confirmingDeleteRole.value = null; },
    });
};

const formatDate = (dateString: string) => {
    if (!dateString) return '—';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    } catch {
        return dateString;
    }
};
</script>

<template>
    <Head title="Role Management" />

    <div class="flex flex-1 flex-col gap-6 p-6 mx-auto w-full max-w-5xl">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-3">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <Shield class="h-6 w-6 text-primary" /> Role Management
                    </h2>
                    <p class="text-sm text-muted-foreground mt-1">
                        Manage access roles and their permission levels. Core system roles are protected.
                    </p>
                </div>
                <AdminAccessNav active="roles" />
            </div>
            <button
                @click="openCreateModal"
                class="inline-flex shrink-0 items-center gap-2 rounded-md bg-primary text-primary-foreground text-sm font-medium shadow hover:bg-primary/90 h-9 px-4 transition-colors"
            >
                <Plus class="h-4 w-4" /> Create Role
            </button>
        </div>

        <!-- Flash messages -->
        <div v-if="($page.props.flash as any)?.success"
            class="flex items-center gap-3 rounded-md border border-emerald-200 bg-emerald-50 p-4 dark:bg-emerald-950/40 dark:border-emerald-900">
            <CheckCircle2 class="h-5 w-5 text-emerald-500 shrink-0" />
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ ($page.props.flash as any).success }}</p>
        </div>
        <div v-if="($page.props.errors as any)?.error"
            class="flex items-center gap-3 rounded-md border border-rose-200 bg-rose-50 p-4 dark:bg-rose-950/40 dark:border-rose-900">
            <ShieldAlert class="h-5 w-5 text-rose-500 shrink-0" />
            <p class="text-sm font-medium text-rose-800 dark:text-rose-200">{{ ($page.props.errors as any).error }}</p>
        </div>

        <!-- Protected levels explainer -->
        <div class="rounded-lg border border-amber-200 bg-amber-50/50 p-4 dark:bg-amber-900/10 dark:border-amber-800">
            <div class="flex gap-2 text-sm text-amber-800 dark:text-amber-300">
                <Lock class="h-4 w-4 mt-0.5 shrink-0" />
                <div>
                    <span class="font-semibold">Core system roles (levels {{ protectedLevels.join(', ') }}) are locked.</span>
                    They are used directly by authorization policies and cannot be renamed, leveled, or deleted.
                    Only custom roles (levels 11–99, excluding reserved) can be freely managed.
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
                            <th class="px-6 py-4 font-semibold">Created Date</th>
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
                                <div class="flex items-center gap-2">
                                    <span :class="['inline-flex items-center border rounded-full px-2.5 py-0.5 text-xs font-bold font-mono', levelBadge(role.level)]">
                                        {{ role.level }} 
                                    </span>
                                    <span class="text-xs text-muted-foreground">{{ levelLabel(role.level) }}</span>
                                </div>
                            </td>

                            <!-- Users count -->
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-medium">{{ role.users_count }}</span>
                                <span class="text-xs text-muted-foreground ml-1">{{ role.users_count === 1 ? 'user' : 'users' }}</span>
                            </td>

                            <!-- Created Date -->
                            <td class="px-6 py-4 text-muted-foreground">
                                {{ formatDate(role.created_at) }}
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div v-if="!isProtected(role.level)" class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(role)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                                        title="Edit role">
                                        <Pencil class="h-4 w-4" />
                                    </button>

                                    <!-- Delete button triggers modal -->
                                    <button 
                                        type="button"
                                        @click="confirmDelete(role)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-muted-foreground hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-colors dark:hover:bg-rose-900/20"
                                        title="Delete role">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                                <span v-else class="text-xs text-muted-foreground italic mr-2">Protected</span>
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

    <!-- Create/Edit Modal -->
    <Dialog v-model:open="isModalOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ modalMode === 'create' ? 'Create New Role' : 'Edit Role' }}</DialogTitle>
                <DialogDescription>
                    {{ modalMode === 'create' ? 'Create a custom role. The level must be between 11 and 99.' : 'Modify custom role details. The level must be between 11 and 99.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm" class="space-y-4 py-4">
                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium leading-none">Role Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="e.g. Moderator"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        required
                        autofocus
                    />
                    <p v-if="form.errors.name" class="text-[0.8rem] font-medium text-destructive">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label for="level" class="text-sm font-medium leading-none">Level Segment (11 - 99)</label>
                    <input
                        id="level"
                        v-model="form.level"
                        type="number"
                        min="11"
                        max="99"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        required
                    />
                    <p v-if="form.errors.level" class="text-[0.8rem] font-medium text-destructive">
                        {{ form.errors.level }}
                    </p>
                    <p class="text-[0.8rem] text-muted-foreground border-l-[3px] border-amber-400 pl-2 mt-1">
                        Avoid core levels: {{ protectedLevels.join(', ') }}
                    </p>
                </div>

                <DialogFooter class="pt-4 sm:justify-between">
                    <button
                        type="button"
                        class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
                        @click="isModalOpen = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
                    >
                        {{ modalMode === 'create' ? 'Create' : 'Save Changes' }}
                    </button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <ConfirmModal
        :open="!!confirmingDeleteRole"
        @update:open="val => { if(!val) confirmingDeleteRole = null }"
        title="Delete Role"
        :description="`Are you sure you want to delete the \'${confirmingDeleteRole?.name}\' role? This action cannot be torn back if users are assigned to it. `"
        confirmText="Delete Role"
        cancelText="Cancel"
        danger
        :loading="deleteForm.processing"
        @confirm="executeDeleteRole"
        @cancel="confirmingDeleteRole = null"
    />
</template>
