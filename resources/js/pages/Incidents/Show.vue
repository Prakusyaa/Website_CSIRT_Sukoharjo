<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    FileText,
    FolderOpen,
    Loader2,
    Mail,
    Pencil,
    Shield,
    Tag,
    Trash2,
    User,
    Users,
} from 'lucide-vue-next';

interface AttachmentRow {
    id: number;
    file_name: string;
    file_type: string | null;
    file_size: number | null;
}

const props = defineProps<{
    incident: {
        id: number;
        subject: string;
        description: string;
        status: string;
        reporter_type: 'internal' | 'external';
        reporter_email: string | null;
        category?: { id: number; name: string };
        severity?: { id: number; name: string; level: number };
        reporter?: { id: number; name: string; username?: string };
        assignee?: { id: number; name: string } | null;
        creator?: { id: number; name: string };
        attachments?: AttachmentRow[];
        created_at?: string;
        updated_at?: string;
    };
}>();

const page = usePage();
const permissions = computed(() => page.props.auth.permissions);
const flash = computed(() => page.props.flash as { success?: string } | undefined);

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    validated: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    in_progress: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    resolved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    closed: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
};

const formatDate = (isoString?: string) => {
    if (!isoString) return '—';
    return (
        new Date(isoString).toLocaleDateString() +
        ' ' +
        new Date(isoString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    );
};

const formatBytes = (n: number | null | undefined) => {
    if (n == null || n <= 0) return '—';
    const units = ['B', 'KB', 'MB', 'GB'];
    let v = n;
    let i = 0;
    while (v >= 1024 && i < units.length - 1) {
        v /= 1024;
        i++;
    }
    return `${v.toFixed(i === 0 ? 0 : 1)} ${units[i]}`;
};

const irLabel = computed(() => `IR-${props.incident.id.toString().padStart(4, '0')}`);

const downloadUrl = (attachmentId: number) =>
    `/incidents/${props.incident.id}/attachments/${attachmentId}/download`;

const deleting = ref(false);

const confirmDelete = () => {
    if (
        !confirm(
            'Archive this incident? It will be hidden from the directory but can be restored in the database if needed.',
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
    <Head :title="`${irLabel} — ${incident.subject}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div v-if="flash?.success" class="rounded-md border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/40">
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ flash.success }}</p>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex flex-col gap-2">
                <Link
                    href="/incidents"
                    class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to directory
                </Link>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold tracking-tight">{{ irLabel }}</h2>
                    <span
                        :class="[
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            statusColors[incident.status] ?? 'bg-muted text-muted-foreground',
                        ]"
                    >
                        {{ incident.status.replace('_', ' ').toUpperCase() }}
                    </span>
                </div>
                <p class="text-lg font-medium text-foreground">{{ incident.subject }}</p>
            </div>
            <div
                v-if="permissions?.can_manage_reports || permissions?.can_delete_incidents"
                class="flex shrink-0 flex-wrap items-center gap-2"
            >
                <Link
                    v-if="permissions?.can_manage_reports"
                    :href="`/incidents/${incident.id}/edit`"
                    class="inline-flex items-center justify-center gap-2 rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-muted"
                >
                    <Pencil class="h-4 w-4" />
                    Edit
                </Link>
                <button
                    v-if="permissions?.can_delete_incidents"
                    type="button"
                    :disabled="deleting"
                    class="inline-flex items-center justify-center gap-2 rounded-md border border-destructive/40 bg-destructive/5 px-4 py-2 text-sm font-medium text-destructive shadow-sm transition-colors hover:bg-destructive/10 disabled:cursor-not-allowed disabled:opacity-60"
                    @click="confirmDelete"
                >
                    <Loader2 v-if="deleting" class="h-4 w-4 animate-spin" />
                    <Trash2 v-else class="h-4 w-4" />
                    {{ deleting ? 'Archiving…' : 'Archive' }}
                </button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                    <div class="border-b px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <Shield class="h-5 w-5 text-primary" />
                            <h3 class="text-lg font-medium">Description</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="whitespace-pre-wrap text-sm leading-relaxed text-foreground">{{ incident.description }}</p>
                    </div>
                </div>

                <div
                    v-if="incident.attachments && incident.attachments.length > 0"
                    class="rounded-xl border bg-card shadow-sm dark:border-gray-800"
                >
                    <div class="border-b px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <FolderOpen class="h-5 w-5 text-primary" />
                            <h3 class="text-lg font-medium">Attachments</h3>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">Evidence files linked to this incident.</p>
                    </div>
                    <ul class="divide-y dark:divide-gray-800">
                        <li
                            v-for="file in incident.attachments"
                            :key="file.id"
                            class="flex flex-wrap items-center justify-between gap-3 px-6 py-4"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <FileText class="h-5 w-5 shrink-0 text-muted-foreground" />
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium" :title="file.file_name">{{ file.file_name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ file.file_type || 'Unknown type' }} · {{ formatBytes(file.file_size) }}
                                    </p>
                                </div>
                            </div>
                            <a
                                :href="downloadUrl(file.id)"
                                class="inline-flex shrink-0 items-center justify-center rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                            >
                                Download
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                    <div class="border-b px-6 py-4 dark:border-gray-800">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Classification</h3>
                    </div>
                    <dl class="space-y-4 p-6">
                        <div class="flex gap-3">
                            <Tag class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Category</dt>
                                <dd class="text-sm font-medium">{{ incident.category?.name ?? '—' }}</dd>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Shield class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Severity</dt>
                                <dd class="text-sm font-medium">
                                    {{ incident.severity?.name ?? 'Triage pending' }}
                                </dd>
                            </div>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                    <div class="border-b px-6 py-4 dark:border-gray-800">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">People</h3>
                    </div>
                    <dl class="space-y-4 p-6">
                        <div class="flex gap-3">
                            <User class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Reporter</dt>
                                <dd class="text-sm font-medium">
                                    {{ incident.reporter_type === 'internal'
                                        ? (incident.reporter?.name ?? '—')
                                        : (incident.reporter_email ?? '—') }}
                                </dd>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Users class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Assignee</dt>
                                <dd class="text-sm font-medium">{{ incident.assignee?.name ?? 'Unassigned' }}</dd>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <User class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Logged by</dt>
                                <dd class="text-sm font-medium">{{ incident.creator?.name ?? '—' }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                    <div class="border-b px-6 py-4 dark:border-gray-800">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Timeline</h3>
                    </div>
                    <dl class="space-y-4 p-6">
                        <div class="flex gap-3">
                            <Calendar class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Logged</dt>
                                <dd class="text-sm text-muted-foreground">{{ formatDate(incident.created_at) }}</dd>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Calendar class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Last updated</dt>
                                <dd class="text-sm text-muted-foreground">{{ formatDate(incident.updated_at) }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</template>
