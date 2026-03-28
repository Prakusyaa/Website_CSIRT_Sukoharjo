<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { FileStack, Clock, Database, User, Activity } from 'lucide-vue-next';

interface UserProfile {
    id: number;
    name: string;
    email: string;
    role_id: number;
}

interface AuditLog {
    id: number;
    user_id: number | null;
    user?: UserProfile;
    action: string;
    table_name: string;
    record_id: number | string;
    changes: Record<string, unknown> | null;
    created_at: string;
}

interface PaginatedData {
    data: AuditLog[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    from: number;
    to: number;
    total: number;
}

defineProps<{
    logs: PaginatedData;
}>();

const breadcrumbs = [
    { title: 'Admin Controls', href: '/dashboard' },
    { title: 'System Audit Logs', href: '/audit-logs' },
];

const formatTime = (date: string) =>
    new Intl.DateTimeFormat('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
        hour12: false,
    }).format(new Date(date));

const actionBadgeClass = (action: string) => {
    switch (action) {
        case 'create': return 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800';
        case 'update': return 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800';
        case 'delete': return 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800';
        default:       return 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700';
    }
};

const safeStr = (v: unknown): string => {
    if (v === null || v === undefined) return 'null';
    if (typeof v === 'object') return JSON.stringify(v);
    return String(v);
};
</script>

<template>
    <Head title="System Audit Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6 mx-auto w-full max-w-7xl">

            <!-- Header -->
            <div>
                <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                    <FileStack class="h-6 w-6 text-primary" />
                    System Audit Logs
                </h2>
                <p class="text-sm text-muted-foreground mt-1">Chronological record of all system mutations. Visible to Admins only.</p>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden dark:border-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-muted-foreground bg-muted/50 border-b dark:bg-gray-900/50 dark:border-gray-800">
                            <tr>
                                <th class="px-5 py-4 font-semibold whitespace-nowrap">Timestamp</th>
                                <th class="px-5 py-4 font-semibold whitespace-nowrap">Actor</th>
                                <th class="px-5 py-4 font-semibold whitespace-nowrap">Resource</th>
                                <th class="px-5 py-4 font-semibold whitespace-nowrap">Action</th>
                                <th class="px-5 py-4 font-semibold w-full">Changes (JSON)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border-b dark:divide-gray-800 dark:border-gray-800">

                            <!-- Empty state -->
                            <tr v-if="logs.data.length === 0">
                                <td colspan="5" class="px-5 py-12 text-center text-muted-foreground">
                                    <Database class="mx-auto h-8 w-8 mb-3 opacity-40" />
                                    No audit logs recorded yet.
                                </td>
                            </tr>

                            <tr
                                v-for="log in logs.data"
                                :key="log.id"
                                class="align-top bg-card hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors"
                            >
                                <!-- Timestamp -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                        <Clock class="h-3.5 w-3.5 shrink-0" />
                                        {{ formatTime(log.created_at) }}
                                    </div>
                                    <div class="mt-1 font-mono text-[10px] text-muted-foreground/50">#{{ log.id }}</div>
                                </td>

                                <!-- Actor -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div v-if="log.user" class="flex items-center gap-2">
                                        <div class="h-7 w-7 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold shrink-0">
                                            {{ log.user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-foreground text-xs">{{ log.user.name }}</div>
                                            <div class="text-[11px] text-muted-foreground">{{ log.user.email }}</div>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center gap-1.5 text-xs text-muted-foreground italic">
                                        <Activity class="h-3.5 w-3.5 opacity-50" /> System
                                    </div>
                                </td>

                                <!-- Resource -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 text-xs font-medium">
                                        <Database class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                                        {{ log.table_name }}
                                    </div>
                                    <div class="mt-0.5 text-[11px] text-muted-foreground">
                                        ID: <code class="bg-muted px-1 rounded">{{ log.record_id }}</code>
                                    </div>
                                </td>

                                <!-- Action badge -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span
                                        :class="['inline-flex items-center border px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize', actionBadgeClass(log.action)]"
                                    >
                                        {{ log.action }}
                                    </span>
                                </td>

                                <!-- Changes: structured JSON viewer -->
                                <td class="px-5 py-4 max-w-xl">
                                    <div v-if="!log.changes" class="text-xs italic text-muted-foreground">—</div>
                                    <div v-else class="text-xs space-y-2">

                                        <!-- create / delete: pretty-print the whole object -->
                                        <div
                                            v-if="log.action !== 'update'"
                                            class="overflow-x-auto rounded-md border bg-gray-50 p-3 dark:bg-gray-950/50 dark:border-gray-800"
                                        >
                                            <pre class="font-mono text-[11px] leading-snug text-gray-800 dark:text-gray-300">{{ JSON.stringify(log.changes, null, 2) }}</pre>
                                        </div>

                                        <!-- update: old → new per key -->
                                        <div v-else class="space-y-1.5">
                                            <div
                                                v-for="(delta, key) in log.changes"
                                                :key="key"
                                                class="rounded-md border bg-gray-50/50 p-2 dark:bg-gray-900/20 dark:border-gray-800"
                                            >
                                                <div class="text-[11px] font-semibold text-foreground mb-1 border-b pb-0.5 dark:border-gray-800">{{ key }}</div>
                                                <div class="grid grid-cols-[3rem_1fr] items-start gap-x-2 gap-y-0.5">
                                                    <span class="text-[10px] font-bold text-rose-600 bg-rose-50 dark:bg-rose-900/30 px-1 py-0.5 rounded text-center">OLD</span>
                                                    <code class="font-mono text-[11px] text-muted-foreground break-all">{{ safeStr((delta as any).old) }}</code>

                                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 px-1 py-0.5 rounded text-center">NEW</span>
                                                    <code class="font-mono text-[11px] text-foreground break-all bg-emerald-50/50 dark:bg-emerald-900/10 rounded px-0.5">{{ safeStr((delta as any).new) }}</code>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="logs.data.length > 0"
                    class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 border-t bg-muted/20 dark:border-gray-800"
                >
                    <span class="text-sm text-muted-foreground">
                        Showing
                        <span class="font-medium text-foreground">{{ logs.from }}</span>–<span class="font-medium text-foreground">{{ logs.to }}</span>
                        of <span class="font-medium text-foreground">{{ logs.total }}</span> entries
                    </span>

                    <nav class="flex items-center gap-1">
                        <template v-for="(link, i) in logs.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2.5 text-sm transition-colors shadow-sm dark:border-gray-700"
                                :class="link.active
                                    ? 'bg-primary text-primary-foreground font-semibold border-primary hover:bg-primary/90'
                                    : 'bg-background text-muted-foreground hover:bg-muted hover:text-foreground'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2.5 text-sm text-muted-foreground opacity-40 cursor-not-allowed bg-background dark:border-gray-700"
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
