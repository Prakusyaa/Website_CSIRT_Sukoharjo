<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, ChevronUp, ChevronDown, Filter, FileText } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    incidents: {
        data: any[];
        links: any;
        meta: any;
    };
    filters: {
        search?: string;
        status?: string;
        sort?: string;
        direction?: string;
    };
}>();

// Breadcrumbs
const breadcrumbs = [
    { title: 'Incidents', href: '/incidents' },
];

// Reactive states hydrated from Inertia response
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const sortField = ref(props.filters.sort || 'created_at');
const sortDirection = ref(props.filters.direction || 'desc');

// Vanilla JS Debounce helper for typing performance
let searchTimeout: ReturnType<typeof setTimeout>;

watch([search, status], ([newSearch, newStatus]) => {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        router.get('/incidents', {
            search: newSearch,
            status: newStatus,
            sort: sortField.value,
            direction: sortDirection.value,
        }, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }, 300); // 300ms debounce
});

// Trigger sorting updates
const toggleSort = (field: string) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }

    router.get('/incidents', {
        search: search.value,
        status: status.value,
        sort: sortField.value,
        direction: sortDirection.value,
    }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};

// Styling helpers
const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    validated: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    in_progress: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    resolved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    closed: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
};

const formatDate = (isoString?: string) => {
    if (!isoString) return '--';
    return new Date(isoString).toLocaleDateString() + ' ' + new Date(isoString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Incidents Workbench" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div class="flex flex-col gap-1">
                    <h2 class="text-2xl font-bold tracking-tight">Incidents Directory</h2>
                    <p class="text-sm text-muted-foreground">Manage and filter through all operational security incidents.</p>
                </div>
                <div>
                    <Link href="/incidents/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90">
                        Log New Incident
                    </Link>
                </div>
            </div>

            <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                <!-- Filters Bar -->
                <div class="flex flex-col gap-4 border-b p-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Search subjects or reporters..." 
                            class="w-full rounded-md border border-input bg-background py-2 pl-9 pr-4 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        />
                    </div>
                    <div class="flex w-full sm:w-auto items-center gap-2">
                        <Filter class="h-4 w-4 text-muted-foreground" />
                        <select v-model="status" class="w-full sm:w-48 rounded-md border border-input px-3 py-2 text-sm bg-background focus:outline-none focus:ring-1 focus:ring-ring">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending Triage</option>
                            <option value="validated">Validated</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed / Archived</option>
                            <option value="rejected">Rejected (False Positive)</option>
                        </select>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm max-w-full">
                        <thead class="[&_tr]:border-b dark:border-gray-800">
                            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground cursor-pointer" @click="toggleSort('id')">
                                    <div class="flex items-center gap-1">ID <span v-if="sortField === 'id'"><component :is="sortDirection === 'asc' ? ChevronUp : ChevronDown" class="h-4 w-4" /></span></div>
                                </th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground cursor-pointer" @click="toggleSort('subject')">
                                    <div class="flex items-center gap-1">Subject <span v-if="sortField === 'subject'"><component :is="sortDirection === 'asc' ? ChevronUp : ChevronDown" class="h-4 w-4" /></span></div>
                                </th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground cursor-pointer" @click="toggleSort('status')">
                                    <div class="flex items-center gap-1">Status <span v-if="sortField === 'status'"><component :is="sortDirection === 'asc' ? ChevronUp : ChevronDown" class="h-4 w-4" /></span></div>
                                </th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Reporter</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Severity</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground cursor-pointer" @click="toggleSort('created_at')">
                                    <div class="flex items-center gap-1">Logged At <span v-if="sortField === 'created_at'"><component :is="sortDirection === 'asc' ? ChevronUp : ChevronDown" class="h-4 w-4" /></span></div>
                                </th>
                                <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            <tr v-for="incident in incidents.data" :key="incident.id" class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted dark:border-gray-800">
                                <td class="p-4 align-middle font-medium">IR-{{ incident.id.toString().padStart(4, '0') }}</td>
                                <td class="p-4 align-middle max-w-md truncate" :title="incident.subject">{{ incident.subject }}</td>
                                <td class="p-4 align-middle">
                                    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold', statusColors[incident.status]]">
                                        {{ incident.status.replace('_', ' ').toUpperCase() }}
                                    </span>
                                </td>
                                <td class="p-4 align-middle text-muted-foreground">{{ incident.reporter?.name || incident.reporter_email || 'System' }}</td>
                                <td class="p-4 align-middle">
                                    <span v-if="incident.severity" class="text-xs font-semibold">{{ incident.severity.name }}</span>
                                    <span v-else class="text-xs text-muted-foreground italic">Triage pending</span>
                                </td>
                                <td class="p-4 align-middle text-muted-foreground">{{ formatDate(incident.created_at) }}</td>
                                <td class="p-4 align-middle text-right">
                                    <Link :href="`/incidents/${incident.id}`" class="text-primary hover:underline text-sm font-medium">View</Link>
                                </td>
                            </tr>
                            
                            <tr v-if="incidents.data.length === 0">
                                <td colspan="7" class="p-8 text-center text-muted-foreground">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <FileText class="h-8 w-8 opacity-40" />
                                        <p>No incidents found matching these criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer -->
                <div class="flex items-center justify-between border-t p-4 dark:border-gray-800" v-if="incidents.meta.last_page > 1">
                    <p class="text-sm text-muted-foreground">
                        Showing {{ incidents.meta.from }} to {{ incidents.meta.to }} of {{ incidents.meta.total }} entries
                    </p>
                    <div class="flex items-center space-x-2">
                        <Link 
                            v-for="(link, idx) in incidents.meta.links"
                            :key="idx"
                            :href="link.url || '#'"
                            class="inline-flex items-center justify-center rounded-md border px-3 py-1 text-sm shadow-sm transition-colors"
                            :class="[
                                link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-background hover:bg-muted dark:border-gray-700',
                                !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
