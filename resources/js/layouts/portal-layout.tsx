import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import AppLogo from '@/components/app-logo';
import LanguageSwitch from '@/components/language-switch';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { UserMenuContent } from '@/components/user-menu-content';
import { useTranslation } from '@/lib/i18n';
import { dashboard } from '@/routes/portal';

/**
 * Company Portal layout (D-061): mobile-first, a plain header with the
 * language switch and the account menu.
 */
export default function PortalLayout({ children }: PropsWithChildren) {
    const { auth, flash } = usePage<{
        flash: { success?: string; error?: string };
    }>().props;
    const { t } = useTranslation();

    return (
        <div className="flex min-h-svh flex-col bg-background">
            <header className="border-b">
                <div className="mx-auto flex h-14 max-w-5xl items-center gap-2 px-4">
                    <Link
                        href={dashboard()}
                        className="flex items-center gap-2"
                        aria-label={t('Dashboard')}
                    >
                        <AppLogo />
                    </Link>
                    <div className="ml-auto flex items-center gap-1">
                        <LanguageSwitch />
                        {auth.user && (
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        className="h-10 gap-2 px-2"
                                        data-test="portal-menu-button"
                                    >
                                        <UserInfo user={auth.user} />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    className="w-56"
                                    align="end"
                                >
                                    <UserMenuContent user={auth.user} />
                                </DropdownMenuContent>
                            </DropdownMenu>
                        )}
                    </div>
                </div>
            </header>
            <main className="mx-auto w-full max-w-5xl flex-1 px-4 py-6">
                {flash?.success && (
                    <div
                        role="status"
                        className="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200"
                    >
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div
                        role="alert"
                        className="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200"
                    >
                        {flash.error}
                    </div>
                )}
                {children}
            </main>
        </div>
    );
}
