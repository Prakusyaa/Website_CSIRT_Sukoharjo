<script lang="ts">
import GuestLayout from '@/layouts/GuestLayout.vue';

export default {
    layout: GuestLayout,
};
</script>

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

    <!-- Status Message -->
    <div v-if="status" class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-600 border border-green-200 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
        {{ status }}
    </div>

    <!-- Minimal Form Wrapper inside the GuestLayout slot -->
    <form @submit.prevent="submit" class="space-y-5">
        <div>
            <label for="login" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Username or Email</label>
            <div class="mt-1">
                <input
                    id="login"
                    v-model="form.login"
                    type="text"
                    required
                    autofocus
                    class="block w-full rounded-xl border-zinc-300 px-4 py-3 shadow-sm placeholder:text-zinc-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm transition-all duration-200"
                    placeholder="admin@csirt.local"
                />
            </div>
            <p v-if="form.errors.login" class="mt-2 text-sm font-medium text-red-500">{{ form.errors.login }}</p>
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password</label>
                <!-- Add forgot password link here if wanted -->
            </div>
            <div class="mt-1">
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    class="block w-full rounded-xl border-zinc-300 px-4 py-3 shadow-sm placeholder:text-zinc-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm transition-all duration-200"
                    placeholder="••••••••"
                />
            </div>
            <p v-if="form.errors.password" class="mt-2 text-sm font-medium text-red-500">{{ form.errors.password }}</p>
        </div>

        <div class="flex items-center">
            <input
                id="remember"
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-offset-zinc-900"
            />
            <label for="remember" class="ml-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Remember me</label>
        </div>

        <div class="pt-2">
            <button
                type="submit"
                :disabled="form.processing"
                class="flex w-full items-center justify-center rounded-xl border border-transparent bg-zinc-900 px-4 py-3 text-sm font-medium text-white shadow-md hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-500 dark:focus:ring-offset-zinc-900 transition-all duration-200 ease-in-out"
            >
                <Loader2 v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" />
                <span v-else class="mr-2 flex h-5 w-5 items-center justify-center">
                    <LogIn class="h-4 w-4" />
                </span>
                Sign In to CSIRT
            </button>
        </div>
    </form>
</template>
