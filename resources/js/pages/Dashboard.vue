<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const logoutForm = useForm({});

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
        </div>
    </div>
</template>
