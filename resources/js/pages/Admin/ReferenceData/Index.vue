<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import { Tags, Plus, Pencil, Trash2, CheckCircle2, ShieldAlert } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmModal from '@/components/ui/ConfirmModal.vue';

interface Category {
    id: number;
    name: string;
}

interface Severity {
    id: number;
    name: string;
    level: number;
}

const props = defineProps<{
    categories: Category[];
    severities: Severity[];
}>();

const categoryCreate = useForm({ name: '' });
const severityCreate = useForm({ name: '', level: 0 as number });

const editingCategoryId = ref<number | null>(null);
const categoryEdit = useForm({ name: '' });

const editingSeverityId = ref<number | null>(null);
const severityEdit = useForm({ name: '', level: 0 as number });

const confirmingDeleteCategory = ref<Category | null>(null);
const confirmingDeleteSeverity = ref<Severity | null>(null);

const deleteCategoryForm = useForm({});
const deleteSeverityForm = useForm({});

const startEditCategory = (c: Category) => {
    editingCategoryId.value = c.id;
    categoryEdit.name = c.name;
    categoryEdit.clearErrors();
};

const cancelEditCategory = () => {
    editingCategoryId.value = null;
    categoryEdit.reset();
};

const submitCategoryEdit = (id: number) => {
    categoryEdit.put(`/admin/categories/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingCategoryId.value = null;
            categoryEdit.reset();
        },
    });
};

const startEditSeverity = (s: Severity) => {
    editingSeverityId.value = s.id;
    severityEdit.name = s.name;
    severityEdit.level = s.level;
    severityEdit.clearErrors();
};

const cancelEditSeverity = () => {
    editingSeverityId.value = null;
    severityEdit.reset();
};

const submitSeverityEdit = (id: number) => {
    severityEdit.put(`/admin/severities/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingSeverityId.value = null;
            severityEdit.reset();
        },
    });
};

const submitCategoryCreate = () => {
    categoryCreate.post('/admin/categories', {
        preserveScroll: true,
        onSuccess: () => categoryCreate.reset(),
    });
};

const submitSeverityCreate = () => {
    severityCreate.post('/admin/severities', {
        preserveScroll: true,
        onSuccess: () => {
            severityCreate.reset();
            severityCreate.level = 0;
        },
    });
};

const performDeleteCategory = () => {
    if (!confirmingDeleteCategory.value) return;
    deleteCategoryForm.delete(`/admin/categories/${confirmingDeleteCategory.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            confirmingDeleteCategory.value = null;
        },
    });
};

const performDeleteSeverity = () => {
    if (!confirmingDeleteSeverity.value) return;
    deleteSeverityForm.delete(`/admin/severities/${confirmingDeleteSeverity.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            confirmingDeleteSeverity.value = null;
        },
    });
};
</script>

<template>
    <Head title="Categories & Severities" />

    <div class="flex flex-1 flex-col gap-6 p-6 mx-auto w-full max-w-6xl">
        <div>
            <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                <Tags class="h-6 w-6 text-primary" />
                Categories & Severities
            </h2>
            <p class="text-sm text-muted-foreground mt-1">
                Manage incident classification labels and severity ordering. Deletion is blocked while a value is still linked to incidents.
            </p>
        </div>

        <div v-if="$page.props.flash?.success" class="flex items-center gap-3 rounded-md border border-emerald-200 bg-emerald-50 p-4 dark:bg-emerald-950/40 dark:border-emerald-900">
            <CheckCircle2 class="h-5 w-5 text-emerald-500 shrink-0" />
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ $page.props.flash.success }}</p>
        </div>
        <div v-if="$page.props.errors?.error" class="flex items-center gap-3 rounded-md border border-rose-200 bg-rose-50 p-4 dark:bg-rose-950/40 dark:border-rose-900">
            <ShieldAlert class="h-5 w-5 text-rose-500 shrink-0" />
            <p class="text-sm font-medium text-rose-800 dark:text-rose-200">{{ $page.props.errors.error }}</p>
        </div>

        <div class="grid gap-8 lg:grid-cols-2">
            <!-- Categories -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden dark:border-gray-800">
                <div class="border-b bg-muted/30 px-4 py-3 dark:border-gray-800">
                    <h3 class="font-semibold tracking-tight">Categories</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Used when classifying incident types.</p>
                </div>

                <form class="flex gap-2 border-b p-4 dark:border-gray-800" @submit.prevent="submitCategoryCreate">
                    <input
                        v-model="categoryCreate.name"
                        type="text"
                        placeholder="New category name"
                        class="flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    />
                    <button
                        type="submit"
                        :disabled="categoryCreate.processing"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 disabled:opacity-60"
                    >
                        <Plus class="h-4 w-4" />
                        Add
                    </button>
                </form>
                <p v-if="categoryCreate.errors.name" class="px-4 pb-2 text-xs text-destructive">{{ categoryCreate.errors.name }}</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-muted-foreground bg-muted/50 border-b dark:bg-gray-900/50 dark:border-gray-800">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Name</th>
                                <th class="px-4 py-3 font-semibold text-right w-32">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-800">
                            <tr v-for="c in props.categories" :key="c.id" class="align-middle">
                                <td class="px-4 py-3">
                                    <template v-if="editingCategoryId === c.id">
                                        <input
                                            v-model="categoryEdit.name"
                                            type="text"
                                            class="w-full rounded-md border border-input bg-background px-2 py-1.5 text-sm"
                                        />
                                        <p v-if="categoryEdit.errors.name" class="mt-1 text-xs text-destructive">{{ categoryEdit.errors.name }}</p>
                                    </template>
                                    <template v-else>
                                        <span class="font-medium">{{ c.name }}</span>
                                    </template>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <template v-if="editingCategoryId === c.id">
                                        <button
                                            type="button"
                                            class="mr-2 text-xs text-muted-foreground hover:text-foreground"
                                            @click="cancelEditCategory"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="button"
                                            :disabled="categoryEdit.processing"
                                            class="inline-flex items-center rounded-md border px-2 py-1 text-xs font-medium hover:bg-muted disabled:opacity-60"
                                            @click="submitCategoryEdit(c.id)"
                                        >
                                            Save
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-muted-foreground hover:bg-muted mr-1"
                                            title="Edit"
                                            @click="startEditCategory(c)"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-muted-foreground hover:bg-muted hover:text-destructive"
                                            title="Delete"
                                            @click="confirmingDeleteCategory = c"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>


            </div>

            <!-- Severities -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden dark:border-gray-800">
                <div class="border-b bg-muted/30 px-4 py-3 dark:border-gray-800">
                    <h3 class="font-semibold tracking-tight">Severities</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Numeric level controls sort order (higher = more severe).</p>
                </div>

                <form class="flex flex-col gap-2 border-b p-4 sm:flex-row sm:items-end dark:border-gray-800" @submit.prevent="submitSeverityCreate">
                    <div class="flex-1">
                        <label class="text-xs font-medium text-muted-foreground">Name</label>
                        <input
                            v-model="severityCreate.name"
                            type="text"
                            placeholder="e.g. Critical"
                            class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        />
                    </div>
                    <div class="w-full sm:w-28">
                        <label class="text-xs font-medium text-muted-foreground">Level</label>
                        <input
                            v-model.number="severityCreate.level"
                            type="number"
                            min="0"
                            class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="severityCreate.processing"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 disabled:opacity-60"
                    >
                        <Plus class="h-4 w-4" />
                        Add
                    </button>
                </form>
                <div class="flex flex-wrap gap-x-4 px-4 pb-2">
                    <p v-if="severityCreate.errors.name" class="text-xs text-destructive">{{ severityCreate.errors.name }}</p>
                    <p v-if="severityCreate.errors.level" class="text-xs text-destructive">{{ severityCreate.errors.level }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-muted-foreground bg-muted/50 border-b dark:bg-gray-900/50 dark:border-gray-800">
                            <tr>
                                <th class="px-4 py-3 font-semibold w-24">Level</th>
                                <th class="px-4 py-3 font-semibold">Name</th>
                                <th class="px-4 py-3 font-semibold text-right w-32">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-800">
                            <tr v-for="s in props.severities" :key="s.id" class="align-middle">
                                <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                    <template v-if="editingSeverityId === s.id">
                                        <input
                                            v-model.number="severityEdit.level"
                                            type="number"
                                            min="0"
                                            class="w-full rounded-md border border-input bg-background px-2 py-1.5 text-sm"
                                        />
                                    </template>
                                    <template v-else>
                                        {{ s.level }}
                                    </template>
                                </td>
                                <td class="px-4 py-3">
                                    <template v-if="editingSeverityId === s.id">
                                        <input
                                            v-model="severityEdit.name"
                                            type="text"
                                            class="w-full rounded-md border border-input bg-background px-2 py-1.5 text-sm"
                                        />
                                        <p v-if="severityEdit.errors.name" class="mt-1 text-xs text-destructive">{{ severityEdit.errors.name }}</p>
                                        <p v-if="severityEdit.errors.level" class="mt-1 text-xs text-destructive">{{ severityEdit.errors.level }}</p>
                                    </template>
                                    <template v-else>
                                        <span class="font-medium">{{ s.name }}</span>
                                    </template>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <template v-if="editingSeverityId === s.id">
                                        <button
                                            type="button"
                                            class="mr-2 text-xs text-muted-foreground hover:text-foreground"
                                            @click="cancelEditSeverity"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="button"
                                            :disabled="severityEdit.processing"
                                            class="inline-flex items-center rounded-md border px-2 py-1 text-xs font-medium hover:bg-muted disabled:opacity-60"
                                            @click="submitSeverityEdit(s.id)"
                                        >
                                            Save
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-muted-foreground hover:bg-muted mr-1"
                                            title="Edit"
                                            @click="startEditSeverity(s)"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-muted-foreground hover:bg-muted hover:text-destructive"
                                            title="Delete"
                                            @click="confirmingDeleteSeverity = s"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <ConfirmModal
        :open="!!confirmingDeleteCategory"
        @update:open="val => { if (!val) confirmingDeleteCategory = null }"
        title="Delete Category"
        :description="`Are you sure you want to delete the category '${confirmingDeleteCategory?.name}'?`"
        confirmText="Delete"
        cancelText="Cancel"
        danger
        :loading="deleteCategoryForm.processing"
        @confirm="performDeleteCategory"
        @cancel="confirmingDeleteCategory = null"
    />

    <ConfirmModal
        :open="!!confirmingDeleteSeverity"
        @update:open="val => { if (!val) confirmingDeleteSeverity = null }"
        title="Delete Severity"
        :description="`Are you sure you want to delete the severity '${confirmingDeleteSeverity?.name}'?`"
        confirmText="Delete"
        cancelText="Cancel"
        danger
        :loading="deleteSeverityForm.processing"
        @confirm="performDeleteSeverity"
        @cancel="confirmingDeleteSeverity = null"
    />
</template>
