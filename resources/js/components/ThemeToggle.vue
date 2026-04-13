<script setup lang="ts">
import { useTheme } from '@/composables/useTheme';
import { Sun, Moon } from 'lucide-vue-next';

const { theme, toggleTheme } = useTheme();
</script>

<template>
    <button
        @click="toggleTheme"
        class="inline-flex items-center justify-center bg-transparent p-0 text-muted-foreground hover:text-foreground transition-colors cursor-pointer focus:outline-none"
        aria-label="Toggle Theme"
    >
        <Sun class="size-4" :class="theme === 'light' ? 'block' : 'hidden'" />
        <Moon class="size-4" :class="theme === 'dark' ? 'block' : 'hidden'" />
    </button>
</template>
