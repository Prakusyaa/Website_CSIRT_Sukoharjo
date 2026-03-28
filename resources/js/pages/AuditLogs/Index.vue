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
    changes: Record<string, any> | null;
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

const formatTime = (date: string) => {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
        hour12: false
    }).format(new Date(date));
};

const getActionColor = (action: string) => {
    switch (action.toLowerCase()) {
        case 'create': return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';
        case 'update': return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800';
        case 'delete': return 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400 border-rose-200 dark:border-rose-800';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700';
    }
};

// Formats arbitrary mixed JSON values into a read-safe text string cleanly
const stringifyValue = (val: any) => {
    if (val === null || val === undefined) return 'null';
    if (typeof val === 'object') return JSON.stringify(val);
    return String(val);
};

</script>

<template>
    <Head title="System Audit Logs - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6 mx-auto w-full max-w-7xl">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <FileStack class="h-6 w-6 text-primary" />
                        System Audit Logs
                    </h2>
                    <p class="text-sm text-muted-foreground mt-1">Chronological oversight tracking core application mutations natively.</p>
                </div>
            </div>
            
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden dark:border-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left whitespace-nowrap">
                        <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b dark:bg-gray-900/50 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold w-48">Timestamp</th>
                                <th scope="col" class="px-6 py-4 font-semibold w-64">Actor</th>
                                <th scope="col" class="px-6 py-4 font-semibold w-56">Resource</th>
                                <th scope="col" class="px-6 py-4 font-semibold w-32">Action</th>
                                <th scope="col" class="px-6 py-4 font-semibold w-full">Detailed Changes (JSON)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border-b dark:border-gray-800 dark:divide-gray-800">
                            <tr v-if="logs.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-muted-foreground bg-background">
                                    <Database class="mx-auto h-8 w-8 text-muted-foreground/50 mb-3" />
                                    No audit traces recorded yet.
                                </td>
                            </tr>
                            <tr v-for="log in logs.data" :key="log.id" class="bg-card hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors align-top">
                                
                                <td class="px-6 py-4 text-xs">
                                    <div class="flex items-center gap-1.5 text-muted-foreground">
                                        <Clock class="h-3 w-3" />
                                        {{ formatTime(log.created_at) }}
                                    </div>
                                    <div class="mt-1 font-mono text-[10px] text-muted-foreground/60 w-full truncate">
                                        ID: {{ log.id }}
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div v-if="log.user" class="flex items-center gap-2 font-medium">
                                        <User class="h-4 w-4 text-muted-foreground" />
                                        {{ log.user.name }}
                                        <span v-if="log.user.role_id >= 100" class="inline-flex items-center justify-center rounded bg-primary/10 px-1 py-0.5 text-[10px] uppercase font-bold text-primary ml-1">Admin</span>
                                    </div>
                                    <div v-else class="flex items-center gap-2 text-muted-foreground italic">
                                        <Activity class="h-4 w-4 opacity-50" /> System Action
                                    </div>
                                    <div v-if="log.user" class="text-xs text-muted-foreground mt-0.5 ml-6">{{ log.user.email }}</div>
                                </td>

                                <td class="px-6 py-4 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <Database class="h-4 w-4 text-muted-foreground" />
                                        {{ log.table_name }}
                                    </div>
                                    <div class="text-xs text-muted-foreground mt-0.5 ml-5.5">PKey: <span class="font-mono bg-muted px-1 py-0.5 rounded">{{ log.record_id }}</span></div>
                                </td>

                                <td class="px-6 py-4">
                                    <span :class="['inline-flex items-center border px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize', getActionColor(log.action)]">
                                        {{ log.action }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 max-w-xl">
                                    <div v-if="!log.changes" class="text-xs italic text-muted-foreground">No JSON data recorded.</div>
                                    <div v-else class="text-xs space-y-2">
                                        
                                        <!-- For Creation / Deletion where it's a flat object -->
                                        <div v-if="log.action !== 'update'" class="bg-gray-50 border rounded p-3 dark:bg-gray-950/50 dark:border-gray-800 overflow-x-auto custom-scrollbar">
                                            <pre class="font-mono text-[11px] leading-snug whitespace-pre text-gray-800 dark:text-gray-300">{{ JSON.stringify(log.changes, null, 2) }}</pre>
                                        </div>
                                        
                                        <!-- For Updates where we have highly specialized Key => Old/New deltas natively -->
                                        <div v-else class="space-y-1.5">
                                            <div v-for="(delta, key) in log.changes" :key="key" class="flex flex-col gap-1 border rounded p-2 bg-gray-50/50 dark:bg-gray-900/20 dark:border-gray-800">
                                                <div class="font-semibold text-gray-900 dark:text-gray-200 block text-[11px] mb-0.5 border-b pb-1 dark:border-gray-800">{{ key }}</div>
                                                <div class="grid grid-cols-[auto_1fr] items-baseline gap-2">
                                                    <span class="text-[10px] uppercase font-bold text-rose-500 bg-rose-50 dark:bg-rose-950 px-1 py-0.5 rounded w-8 text-center shrink-0">Old</span>
                                                    <span class="font-mono text-[11px] text-muted-foreground break-all whitespace-normal border border-transparent">{{ stringifyValue((delta as any).old) }}</span>
                                                </div>
                                                <div class="grid grid-cols-[auto_1fr] items-baseline gap-2 mt-0.5">
                                                    <span class="text-[10px] uppercase font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950 px-1 py-0.5 rounded w-8 text-center shrink-0">New</span>
                                                    <span class="font-mono text-[11px] text-foreground break-all whitespace-normal bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-800/50 p-0.5 rounded">{{ stringifyValue((delta as any).new) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination logic specifically bridging standard Laravel models into Inertia -->
                <div v-if="logs.data.length > 0" class="flex items-center justify-between px-6 py-4 border-t bg-muted/20 dark:border-gray-800">
                    <span class="text-sm text-muted-foreground">
                        Showing <span class="font-medium text-foreground">{{ logs.from }}</span> to <span class="font-medium text-foreground">{{ logs.to }}</span> of <span class="font-medium text-foreground">{{ logs.total }}</span> logs
                    </span>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in logs.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="inline-flex items-center justify-center h-8 px-3 rounded-md text-sm transition-colors border shadow-sm dark:border-gray-700"
                                :class="[
                                    link.active 
                                    ? 'bg-primary text-primary-foreground font-semibold border-primary hover:bg-primary/90' 
                                    : 'bg-background hover:bg-muted text-muted-foreground hover:text-foreground'
                                ]"
                                v-html="link.label"
                            />
                            <span 
                                v-else 
                                class="inline-flex items-center justify-center h-8 px-3 rounded-md text-sm text-muted-foreground opacity-50 cursor-not-allowed border bg-background dark:border-gray-700" 
                                v-html="link.label"
                            ></span>
                        </template>
                    </div>
                </div>

            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    height: 6px;
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5); /* gray-400 */
    border-radius: 9999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(75, 85, 99, 0.5); /* gray-600 */
}
</style>
