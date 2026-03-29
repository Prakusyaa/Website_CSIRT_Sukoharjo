import { PageProps as InertiaPageProps } from '@inertiajs/core';
import type { Auth } from '@/types/auth';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps {
        name?: string;
        auth: Auth;
        sidebarOpen?: boolean;
        flash?: {
            success?: string | null;
            error?: string | null;
        };
        errors?: Record<string, string> & {
            error?: string | null;
        };
        [key: string]: unknown;
    }
}
