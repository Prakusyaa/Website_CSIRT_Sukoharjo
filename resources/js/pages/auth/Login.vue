<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2, LogIn } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="flex min-h-screen items-center justify-center bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-800">
            <div class="p-8">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <LogIn class="h-6 w-6" />
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Sign in to CSIRT</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Secure internal response system access
                    </p>
                </div>

                <!-- Status Message -->
                <div v-if="status" class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-600 dark:bg-green-900/30 dark:text-green-400">
                    {{ status }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="login" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Username or Email</label>
                        <div class="mt-2">
                            <input
                                id="login"
                                v-model="form.login"
                                type="text"
                                required
                                autofocus
                                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white sm:text-sm transition-colors"
                            />
                        </div>
                        <p v-if="form.errors.login" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.login }}</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Password</label>
                        <div class="mt-2">
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white sm:text-sm transition-colors"
                            />
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:ring-offset-gray-800"
                            />
                            <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Remember me</label>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full justify-center rounded-xl border border-transparent bg-blue-600 py-2.5 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:focus:ring-offset-gray-800 transition-all duration-200"
                    >
                        <Loader2 v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" />
                        Sign in
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
