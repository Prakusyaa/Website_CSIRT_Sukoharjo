<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { AlertCircle, Briefcase, Loader2, Shield, Trash2, User } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
}

interface Severity {
    id: number;
    name: string;
    level: number;
}

interface IncidentPayload {
    id: number;
    subject: string;
    description: string;
    status: string;
    reporter_email: string | null;
    category?: { id: number; name: string };
    severity?: { id: number; name: string; level: number };
    reporter?: { id: number; name: string; username?: string };
    assignee?: { id: number; name: string } | null;
}

const props = defineProps<{
    incident: IncidentPayload;
    categories: Category[];
    severities: Severity[];
    csirtUsers: { id: number; name: string }[];
}>();

const page = usePage();
const permissions = computed(() => page.props.auth.permissions);
const deleting = ref(false);

const form = useForm({
    subject: props.incident.subject,
    description: props.incident.description,
    category_id: (props.incident.category?.id ?? '') as number | '',
    severity_id: (props.incident.severity?.id ?? '') as number | '',
    assigned_to: (props.incident.assignee?.id ?? '') as number | '',
    status: props.incident.status,
});

const statusLabels: Record<string, string> = {
    pending: 'Pending Triage',
    validated: 'Validated',
    in_progress: 'In Progress',
    resolved: 'Resolved',
    rejected: 'Rejected (False Positive)',
    closed: 'Closed / Archived',
};

const statusOptions = computed(() =>
    (Object.keys(statusLabels) as string[]).map((value) => ({
        value,
        label: statusLabels[value],
    })),
);

const showUrl = computed(() => `/incidents/${props.incident.id}`);

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            category_id: data.category_id === '' ? null : data.category_id,
            severity_id: data.severity_id === '' ? null : data.severity_id,
            assigned_to: data.assigned_to === '' ? null : data.assigned_to,
        }))
        .put(`/incidents/${props.incident.id}`, {
            preserveScroll: true,
        });
};

const confirmDelete = () => {
    if (
        !confirm(
            'Archive this incident? It will be removed from the directory but can be restored in the database if needed.',
        )
    ) {
        return;
    }
    deleting.value = true;
    router.delete(`/incidents/${props.incident.id}`, {
        onFinish: () => {
            deleting.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Edit — ${incident.subject}`" />

    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-2">
                <Link
                    :href="showUrl"
                    class="inline-flex w-fit items-center text-sm font-medium text-muted-foreground hover:text-foreground"
                >
                    ← Back to incident
                </Link>
                <h2 class="text-2xl font-bold tracking-tight">Edit incident</h2>
                <p class="text-sm text-muted-foreground">Update classification, assignment, and workflow status.</p>
            </div>
        </div>

        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                <div class="border-b px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <User class="h-5 w-5 text-primary" />
                        <h3 class="text-lg font-medium">Reporter</h3>
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground">Reporter is fixed for this record; contact details stay on file.</p>
                </div>
                <div class="p-6">
                    <p class="text-sm">
                        <span class="font-medium text-foreground">{{ incident.reporter?.name ?? '—' }}</span>
                        <span v-if="incident.reporter_email" class="text-muted-foreground">
                            · {{ incident.reporter_email }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                <div class="border-b px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <Shield class="h-5 w-5 text-primary" />
                        <h3 class="text-lg font-medium">Incident details</h3>
                    </div>
                </div>
                <div class="space-y-6 p-6">
                    <div class="space-y-2">
                        <label for="subject" class="text-sm font-medium leading-none">Subject</label>
                        <input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="form.errors.subject" class="mt-1 flex items-center gap-1 text-[0.8rem] font-medium text-destructive">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.subject }}
                        </p>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label for="category_id" class="text-sm font-medium leading-none">Category</label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            >
                                <option value="">—</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="form.errors.category_id" class="mt-1 flex items-center gap-1 text-[0.8rem] font-medium text-destructive">
                                <AlertCircle class="h-3 w-3" /> {{ form.errors.category_id }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label for="severity_id" class="text-sm font-medium leading-none">Severity</label>
                            <select
                                id="severity_id"
                                v-model="form.severity_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            >
                                <option value="">—</option>
                                <option v-for="s in severities" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                            <p v-if="form.errors.severity_id" class="mt-1 flex items-center gap-1 text-[0.8rem] font-medium text-destructive">
                                <AlertCircle class="h-3 w-3" /> {{ form.errors.severity_id }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="description" class="text-sm font-medium leading-none">Description</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="8"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p v-if="form.errors.description" class="mt-1 flex items-center gap-1 text-[0.8rem] font-medium text-destructive">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.description }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="status" class="text-sm font-medium leading-none">Status</label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                        >
                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.status" class="mt-1 flex items-center gap-1 text-[0.8rem] font-medium text-destructive">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.status }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-indigo-200 bg-indigo-50/30 shadow-sm dark:border-indigo-900/30 dark:bg-indigo-900/10">
                <div class="border-b border-indigo-100 px-6 py-4 dark:border-indigo-900/50">
                    <div class="flex items-center gap-2">
                        <Briefcase class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        <h3 class="text-lg font-medium text-indigo-900 dark:text-indigo-200">Assignment</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-2">
                        <label for="assigned_to" class="text-sm font-medium text-indigo-900 dark:text-indigo-300">Assignee</label>
                        <select
                            id="assigned_to"
                            v-model="form.assigned_to"
                            class="flex h-10 w-full rounded-md border border-indigo-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-indigo-800 dark:bg-gray-950"
                        >
                            <option value="">Unassigned</option>
                            <option v-for="u in csirtUsers" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <p v-if="form.errors.assigned_to" class="mt-1 flex items-center gap-1 text-[0.8rem] font-medium text-destructive">
                            <AlertCircle class="h-3 w-3" /> {{ form.errors.assigned_to }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="permissions?.can_delete_incidents"
                class="rounded-xl border border-destructive/30 bg-destructive/5 p-6 dark:border-destructive/40 dark:bg-destructive/10"
            >
                <h3 class="text-sm font-semibold text-destructive">Archive incident</h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    Soft-delete this record. It will no longer appear in the incidents directory.
                </p>
                <button
                    type="button"
                    :disabled="deleting || form.processing"
                    class="mt-4 inline-flex items-center justify-center gap-2 rounded-md border border-destructive/50 bg-background px-4 py-2 text-sm font-medium text-destructive shadow-sm transition-colors hover:bg-destructive/10 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="confirmDelete"
                >
                    <Loader2 v-if="deleting" class="h-4 w-4 animate-spin" />
                    <Trash2 v-else class="h-4 w-4" />
                    {{ deleting ? 'Archiving…' : 'Archive incident' }}
                </button>
            </div>

            <div class="flex items-center justify-end gap-3 border-t pt-4 dark:border-gray-800">
                <Link
                    :href="showUrl"
                    class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 text-sm font-medium shadow-sm transition-colors hover:bg-muted"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-6 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 disabled:pointer-events-none disabled:opacity-50"
                >
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Save changes
                </button>
            </div>
        </form>
    </div>
</template>
