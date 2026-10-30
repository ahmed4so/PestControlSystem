import { Head, usePage } from '@inertiajs/react';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';

/**
 * Company dashboard. Empty in Phase 1; the company profile comes in Phase 2.
 */
export default function Dashboard() {
    const { auth } = usePage().props;
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Dashboard')} />

            <div className="space-y-2">
                <h1 className="text-xl font-semibold">
                    {t('Welcome, :name', { name: auth.user?.name ?? '' })}
                </h1>
                <p className="text-muted-foreground">
                    {t('Your company dashboard. Your company profile and applications will appear here.')}
                </p>
            </div>
        </PortalLayout>
    );
}
