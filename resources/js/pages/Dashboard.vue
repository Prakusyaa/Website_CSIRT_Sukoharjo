<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Activity, AlertOctagon, CheckCircle2, FileText } from 'lucide-vue-next';

const page = usePage();
const permissions = computed(() => page.props.auth.permissions);
const user = computed(() => page.props.auth.user);

const props = defineProps<{
    stats: {
        total_incidents: number;
        open_incidents: number;
        resolved_incidents: number;
        critical_incidents: number;
    }
}>();

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const cards = computed(() => [
    {
        title: 'Total Incidents',
        value: props.stats?.total_incidents ?? 0,
        description: 'Total recorded reports',
        icon: FileText,
        colorClass: 'text-blue-500 bg-blue-50',
    },
    {
        title: 'Open Investigations',
        value: props.stats?.open_incidents ?? 0,
        description: 'Assigned and pending',
        icon: Activity,
        colorClass: 'text-amber-500 bg-amber-50',
    },
    {
        title: 'Resolved Cases',
        value: props.stats?.resolved_incidents ?? 0,
        description: 'Successfully resolved',
        icon: CheckCircle2,
        colorClass: 'text-emerald-500 bg-emerald-50',
    },
    {
        title: 'Active Critical',
        value: props.stats?.critical_incidents ?? 0,
        description: 'Severity >= 60',
        icon: AlertOctagon,
        colorClass: 'text-red-500 bg-red-50',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
            
            <!-- Welcome Header Section -->
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl font-bold tracking-tight">Welcome back, {{ user.name }}</h2>
                <p class="text-muted-foreground text-sm">
                    Here's a quick overview of the CSIRT system status and pending actionable items.
                </p>
            </div>

            <!-- Future KPI Widgets -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div 
                    v-for="(card, index) in cards" 
                    :key="index"
                    class="rounded-xl border bg-card p-6 shadow-sm transition-all hover:shadow-md"
                >
                    <div class="flex items-center gap-4">
                        <div :class="['flex h-12 w-12 shrink-0 items-center justify-center rounded-lg', card.colorClass]">
                            <component :is="card.icon" class="h-6 w-6" />
                        </div>
                        <div class="flex flex-col">
                            <h3 class="text-sm font-medium text-muted-foreground">{{ card.title }}</h3>
                            <div class="text-2xl font-bold tracking-tight">{{ card.value }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle Main Grids -->
            <div class="grid flex-1 gap-6 lg:grid-cols-3">
                
                <!-- Left Chart Area -->
                <div class="col-span-1 lg:col-span-2 rounded-xl border bg-card shadow-sm">
                    <div class="flex h-full flex-col p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold tracking-tight">Incident Trends</h3>
                        </div>
                        <!-- Chart Placeholder Widget -->
                        <div class="flex flex-1 items-center justify-center rounded-lg border border-dashed border-gray-200 bg-gray-50">
                            <div class="flex flex-col items-center gap-2 text-center">
                                <Activity class="h-8 w-8 text-gray-400 opacity-50" />
                                <p class="text-sm text-gray-500">Trend chart widget area pending final data bindings</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Recent Activity Feed -->
                <div class="col-span-1 rounded-xl border bg-card shadow-sm">
                    <div class="flex h-full flex-col p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold tracking-tight">Recent Activity</h3>
                        </div>
                        <!-- Activity Feed Placeholder -->
                        <div class="flex flex-1 items-center justify-center rounded-lg border border-dashed border-gray-200 bg-gray-50">
                            <div class="flex flex-col items-center gap-2 text-center p-4">
                                <FileText class="h-8 w-8 text-gray-400 opacity-50" />
                                <p class="text-sm text-gray-500">Activity stream layout ready</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
</template>