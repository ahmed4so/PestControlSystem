import type { Translations } from '@/lib/i18n';
import type { Auth } from '@/types/auth';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            area: 'admin' | 'company' | null;
            auth: Auth;
            sidebarOpen: boolean;
            locale: 'en' | 'so';
            translations: Translations;
            [key: string]: unknown;
        };
    }
}
