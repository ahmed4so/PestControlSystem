import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import AppLogo from '@/components/app-logo';
import LanguageSwitch from '@/components/language-switch';
import { home } from '@/routes';

/**
 * Public pages (D-061): SARIS header and the language switch.
 */
export default function PublicLayout({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-svh flex-col bg-background">
            <header className="border-b">
                <div className="mx-auto flex h-14 max-w-5xl items-center px-4">
                    <Link href={home()} className="flex items-center gap-2">
                        <AppLogo />
                    </Link>
                    <div className="ml-auto">
                        <LanguageSwitch />
                    </div>
                </div>
            </header>
            <main className="mx-auto w-full max-w-5xl flex-1 px-4 py-10">
                {children}
            </main>
        </div>
    );
}
