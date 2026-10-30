import { Head, Link, usePage } from '@inertiajs/react';
import StatusMessage from '@/components/auth/status-message';
import { Button } from '@/components/ui/button';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';
import { edit } from '@/routes/portal/company';

/**
 * Company dashboard: profile completeness (D-178). Applications arrive in
 * Phase 4.
 */
export default function Dashboard({ companyName, missing, amendmentRequired }: { companyName: string; missing: string[]; amendmentRequired: boolean }) {
    const { auth } = usePage().props;
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Dashboard')} />

            <div className="space-y-4">
                <h1 className="text-xl font-semibold">{t('Welcome, :name', { name: auth.user?.name ?? '' })}</h1>
                <p className="text-muted-foreground">{companyName}</p>

                {amendmentRequired && (
                    <StatusMessage tone="error" status={t('SARIS removed a person from your profile. Please update your profile and submit an amendment of your license.')} />
                )}

                {missing.length === 0 ? (
                    <StatusMessage status={t('Your company profile is complete.')} />
                ) : (
                    <div className="space-y-3 rounded-md border p-4">
                        <p className="font-medium">{t('Complete your company profile')}</p>
                        <ul className="list-disc space-y-1 pl-5 text-sm">
                            {missing.map((item) => (
                                <li key={item}>{t(item)}</li>
                            ))}
                        </ul>
                        <Button asChild size="sm">
                            <Link href={edit()}>{t('Go to company profile')}</Link>
                        </Button>
                    </div>
                )}
            </div>
        </PortalLayout>
    );
}
