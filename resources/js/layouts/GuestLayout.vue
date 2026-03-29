<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-zinc-50 dark:bg-zinc-950 p-4 transition-colors duration-300">
        <div class="relative w-full max-w-md">
            
            <!-- Modern Glassmorphic Backdrop Decoration -->
            <div class="absolute -inset-1 rounded-3xl bg-gradient-to-tr from-blue-600 to-indigo-500 opacity-20 blur-xl dark:opacity-30"></div>

            <div class="relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-800">
                
                <!-- Premium Header Space -->
                <div class="w-full bg-zinc-50/50 px-8 pt-10 pb-6 text-center backdrop-blur-sm dark:bg-zinc-900/50">
                    <Link href="/" class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-md transition-transform hover:scale-105">
                        <AppLogoIcon class="h-8 w-8 fill-current" />
                    </Link>
                    <h1 class="mt-6 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ title }}</h1>
                    <p v-if="description" class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ description }}
                    </p>
                </div>

                <!-- Slot Content (Forms go here) -->
                <div class="w-full px-8 pb-10 pt-2">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
