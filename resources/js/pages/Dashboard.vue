<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const logoutForm = useForm({});
const page = usePage();

// Pull injected permissions cleanly from middleware 
const permissions = computed(() => page.props.auth.permissions);

const submitLogout = () => {
    logoutForm.post('/logout');
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-gray-100 p-8 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl rounded-xl bg-white p-6 shadow dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold dark:text-white">CSIRT Dashboard</h1>
                <form @submit.prevent="submitLogout">
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                        Log out
                    </button>
                </form>
            </div>
            
            <p class="mt-4 text-gray-600 dark:text-gray-400">
                You are securely logged into the internal response system.
            </p>

            <!-- Role-based UI Sections -->
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Everyone (Viewers/Staff) -->
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <h3 class="font-semibold dark:text-gray-200">Incident Reports</h3>
                    <p class="mt-1 text-sm text-gray-500">View current active incidents and reports.</p>
                    <button class="mt-3 text-sm font-medium text-blue-600 hover:text-blue-500">View Dashboard &rarr;</button>
                </div>

                <!-- CSIRT (Manage Reports) -->
                <div v-if="permissions?.can_manage_reports" class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/30 dark:bg-blue-900/10">
                    <h3 class="font-semibold text-blue-800 dark:text-blue-300">CSIRT Operations</h3>
                    <p class="mt-1 text-sm text-blue-600 dark:text-blue-400">Create, investigate, and manage incidents.</p>
                    <button class="mt-3 text-sm font-medium text-blue-700 hover:text-blue-600 dark:text-blue-300">Open Workbench &rarr;</button>
                </div>

                <!-- Admin (Full Access) -->
                <div v-if="permissions?.is_admin" class="rounded-lg border border-purple-200 bg-purple-50 p-4 dark:border-purple-900/30 dark:bg-purple-900/10">
                    <h3 class="font-semibold text-purple-800 dark:text-purple-300">Administration</h3>
                    <p class="mt-1 text-sm text-purple-600 dark:text-purple-400">Manage users, roles, and master data.</p>
                    <button class="mt-3 text-sm font-medium text-purple-700 hover:text-purple-600 dark:text-purple-300">System Config &rarr;</button>
                </div>
            </div>
        </div>
    </div>
</template>
