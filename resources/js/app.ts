import '../css/app.css';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';

/**
 * Inertia restores the previous page from client history on back/forward without
 * refetching, so list pages can show stale data (e.g. after "Take Case"). After a
 * history navigation lands on the incidents index, refresh list props from the server.
 */
if (typeof window !== 'undefined') {
    let navigationFromHistory = false;

    window.addEventListener('popstate', () => {
        navigationFromHistory = true;
    });

    document.addEventListener('inertia:start', () => {
        navigationFromHistory = false;
    });

    document.addEventListener('inertia:navigate', () => {
        if (!navigationFromHistory) {
            return;
        }
        navigationFromHistory = false;

        if (window.location.pathname !== '/incidents') {
            return;
        }

        router.reload({
            only: ['incidents', 'filters'],
            preserveScroll: true,
        });
    });
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => AppLayout,
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
