import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import { useTranslation } from '@/lib/i18n';
import { login as adminLogin } from '@/routes/admin';
import { login, signup } from '@/routes/portal';

/**
 * Landing page with links to both logins (D-166).
 */
export default function Home() {
    const { t } = useTranslation();

    return (
        <PublicLayout>
            <Head title={t('Welcome')} />

            <div className="mx-auto max-w-2xl space-y-8">
                <div className="space-y-3">
                    <h1 className="text-2xl font-semibold">
                        {t(
                            'Somali Agricultural Regulatory & Inspection Service',
                        )}
                    </h1>
                    <p className="text-muted-foreground">
                        {t(
                            'Apply online for pest control product licenses and import permits.',
                        )}
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="space-y-3 rounded-lg border p-5">
                        <h2 className="font-medium">{t('Companies')}</h2>
                        <p className="text-sm text-muted-foreground">
                            {t(
                                'Create an account or log in to the Company Portal.',
                            )}
                        </p>
                        <div className="flex flex-wrap gap-2">
                            <Button asChild>
                                <Link href={login()}>{t('Log in')}</Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link href={signup()}>
                                    {t('Create an account')}
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <div className="space-y-3 rounded-lg border p-5">
                        <h2 className="font-medium">{t('SARIS staff')}</h2>
                        <p className="text-sm text-muted-foreground">
                            {t('Log in to the Admin Portal.')}
                        </p>
                        <Button asChild variant="outline">
                            <Link href={adminLogin()}>{t('Staff login')}</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
