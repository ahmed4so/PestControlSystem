import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useArea } from '@/lib/area';
import { useTranslation } from '@/lib/i18n';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/admin/appearance';
import { edit as editLoginEmail } from '@/routes/portal/email';
import type { NavItem } from '@/types';

/**
 * Account settings for either portal: staff can't change their own login
 * email (D-172); companies change it through a confirmation link (D-093).
 */
export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentUrl } = useCurrentUrl();
    const { area, routes } = useArea();
    const { t } = useTranslation();

    // When server-side rendering, we only render the layout on the client...
    if (typeof window === 'undefined') {
        return null;
    }

    const sidebarNavItems: NavItem[] = [
        { title: t('Profile'), href: routes.profileEdit(), icon: null },
        ...(area === 'company'
            ? [{ title: t('Login email'), href: editLoginEmail(), icon: null }]
            : []),
        { title: t('Password'), href: routes.userPasswordEdit(), icon: null },
        {
            title: t('Two-factor authentication'),
            href: routes.twoFactorShow(),
            icon: null,
        },
        ...(area === 'admin'
            ? [{ title: t('Appearance'), href: editAppearance(), icon: null }]
            : []),
    ];

    return (
        <div className="px-4 py-6">
            <Heading
                title={t('Settings')}
                description={t('Manage your profile and account settings')}
            />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav
                        className="flex flex-col space-y-1 space-x-0"
                        aria-label={t('Settings')}
                    >
                        {sidebarNavItems.map((item, index) => (
                            <Button
                                key={`${toUrl(item.href)}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('w-full justify-start', {
                                    'bg-muted': isCurrentUrl(item.href),
                                })}
                            >
                                <Link href={item.href}>{item.title}</Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
