<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Loader2, LogIn, Eye, EyeOff, User } from 'lucide-vue-next';
import { ref, computed } from 'vue';

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

const showPassword = ref(false);

const isFormEmpty = computed(() => {
    return !form.login || !form.password;
});
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen flex items-center justify-center bg-zinc-50 dark:bg-zinc-950 p-4 transition-colors duration-300 relative overflow-hidden">
        
        <!-- Full-screen soft background (light blue gradient) -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-blue-100 via-zinc-50 to-zinc-50 dark:from-blue-900/20 dark:via-zinc-950 dark:to-zinc-950 top-0 left-0 -z-10"></div>
        
        <div class="relative w-full max-w-md w-full px-4 sm:px-0">
            <!-- Soft decorative glow behind the card -->
            <div class="absolute -inset-1 rounded-3xl bg-gradient-to-br from-blue-400 to-blue-600 opacity-20 blur-2xl dark:opacity-30"></div>

            <div class="relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-zinc-100 dark:bg-zinc-900 dark:ring-zinc-800 transition-all duration-300">
                
                <!-- Header Section -->
                <div class="w-full px-8 pt-10 pb-6 text-center">
                    <Link href="/" class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg transition-transform hover:scale-105">
                        <User class="h-8 w-8" />
                    </Link>
                    <h1 class="mt-6 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Welcome Back</h1>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        Log in to access your CSIRT dashboard
                    </p>
                </div>

                <!-- Form Content -->
                <div class="w-full px-8 pb-10 pt-2">
                    <!-- Status Message -->
                    <div v-if="status" class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-600 border border-green-200 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
                        {{ status }}
                    </div>
                    
                    <form @submit.prevent="submit" class="space-y-5">
                        
                        <!-- Username / Email -->
                        <div class="space-y-1.5">
                            <label for="login" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Username or Email
                            </label>
                            <div class="relative">
                                <input
                                    id="login"
                                    type="text"
                                    v-model="form.login"
                                    required
                                    autofocus
                                    placeholder="admin@csirt.local"
                                    class="block w-full rounded-xl border-zinc-200 bg-zinc-50 px-4 py-3 text-sm transition-all duration-200 placeholder:text-zinc-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-white dark:focus:bg-zinc-800"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/10': form.errors.login }"
                                />
                            </div>
                            <p v-if="form.errors.login" class="text-sm font-medium text-red-500">{{ form.errors.login }}</p>
                        </div>

                        <!-- Password -->
                        <div class="space-y-1.5">
                            <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Password
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password"
                                    required
                                    placeholder="••••••••"
                                    class="block w-full rounded-xl border-zinc-200 bg-zinc-50 pl-4 pr-12 py-3 text-sm transition-all duration-200 placeholder:text-zinc-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-white dark:focus:bg-zinc-800"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/10': form.errors.password }"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-zinc-400 hover:text-blue-500 focus:outline-none transition-colors"
                                >
                                    <EyeOff v-if="showPassword" class="h-5 w-5" />
                                    <Eye v-else class="h-5 w-5" />
                                </button>
                            </div>
                            <div class="mt-2 flex justify-between items-center">
                                <p v-if="form.errors.password" class="text-sm font-medium text-red-500">{{ form.errors.password }}</p>
                                <div v-else></div> <!-- Spacer -->
                                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                                    Forgot password?
                                </a>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-offset-zinc-900 transition-colors"
                            />
                            <label for="remember" class="ml-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Remember me
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button
                                type="submit"
                                :disabled="isFormEmpty || form.processing"
                                class="flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-medium text-white shadow-md hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 ease-in-out dark:focus:ring-offset-zinc-900"
                            >
                                <Loader2 v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" />
                                <span v-else class="mr-2 flex h-5 w-5 items-center justify-center">
                                    <LogIn class="h-4 w-4" />
                                </span>
                                Sign In to CSIRT
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
