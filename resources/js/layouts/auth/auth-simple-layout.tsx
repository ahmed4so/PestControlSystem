import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import LanguageSwitch from '@/components/language-switch';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="flex min-h-svh flex-col bg-background">
            <div className="flex justify-end p-4">
                <LanguageSwitch />
            </div>
            <div className="flex flex-1 flex-col items-center justify-center gap-6 px-4 pb-10 md:px-10">
                <div className="w-full max-w-sm">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <Link
                                href={home()}
                                className="flex flex-col items-center gap-2 font-medium"
                            >
                                <div className="mb-1 flex h-9 w-9 items-center justify-center rounded-md">
                                    <AppLogoIcon className="size-9 text-[var(--foreground)] dark:text-white" />
                                </div>
                                <span className="text-sm font-semibold tracking-wide">SARIS</span>
                            </Link>

                            <div className="space-y-2 text-center">
                                <h1 className="text-xl font-medium">{title}</h1>
                                <p className="text-center text-sm text-muted-foreground">
                                    {description}
                                </p>
                            </div>
                        </div>
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
