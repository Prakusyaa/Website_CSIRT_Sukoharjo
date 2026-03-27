<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Mail, Search, Filter, Inbox as InboxIcon } from 'lucide-vue-next';

const breadcrumbs = [
    {
        title: 'Inbox',
        href: '/inbox',
    },
];
</script>

<template>
    <Head title="Inbox" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 overflow-hidden p-6 gap-6">
            
            <!-- Left Column: Message List -->
            <div class="flex w-full max-w-sm flex-col rounded-xl border bg-card shadow-sm dark:border-gray-800">
                
                <!-- Inbox Header / Search -->
                <div class="flex flex-col gap-4 border-b p-4 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold tracking-tight">Active Inbox</h2>
                        <button class="rounded-md border p-2 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition-colors">
                            <Filter class="h-4 w-4 text-muted-foreground" />
                        </button>
                    </div>
                    
                    <div class="relative">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                        <input 
                            type="text" 
                            placeholder="Search messages..." 
                            class="w-full rounded-md border border-input bg-background py-2 pl-9 pr-4 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </div>
                </div>

                <!-- Inbox List Skeleton Placeholder -->
                <div class="flex-1 overflow-y-auto p-2">
                    <div class="flex flex-col items-center justify-center h-full text-center gap-2 py-10 opacity-60">
                        <InboxIcon class="h-8 w-8 text-muted-foreground" />
                        <p class="text-sm text-muted-foreground">Inbox layout structure prepared.<br/>Message items will map here.</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Message Detail View -->
            <div class="hidden flex-1 flex-col rounded-xl border bg-card shadow-sm dark:border-gray-800 lg:flex">
                <div class="flex h-full flex-col items-center justify-center p-8 text-center text-muted-foreground">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                        <Mail class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-foreground">No Message Selected</h3>
                    <p class="text-sm max-w-sm mt-2">
                        Select an item from your inbox on the left to read its details, respond, or reassign the incident.
                    </p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
