import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';
import { store } from '@/routes/portal/company';

/**
 * First step of the company profile (03 §4, D-063): the company name and
 * the business registration number.
 */
export default function CompanySetup() {
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Set up your company profile')} />

            <div className="mx-auto max-w-lg space-y-6">
                <div className="space-y-2">
                    <h1 className="text-xl font-semibold">{t('Set up your company profile')}</h1>
                    <p className="text-sm text-muted-foreground">
                        {t('You enter your company details once. Every application uses them.')}
                    </p>
                </div>

                <Form {...store.form()} className="space-y-4">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">{t('Company name')}</Label>
                                <Input id="name" name="name" required maxLength={200} autoFocus />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="business_registration_no">{t('Business registration number')}</Label>
                                <Input id="business_registration_no" name="business_registration_no" required maxLength={60} />
                                <p className="text-xs text-muted-foreground">{t('As shown on your Ministry of Commerce certificate.')}</p>
                                <InputError message={errors.business_registration_no} />
                            </div>

                            <Button type="submit" className="w-full" disabled={processing}>
                                {processing && <Spinner />}
                                {t('Continue')}
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </PortalLayout>
    );
}
