import { Form, Head } from '@inertiajs/react';
import BackLink from '@/components/back-link';
import NativeSelect from '@/components/company/native-select';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';
import { index as backTo, store } from '@/routes/portal/applications';

type Props = { categories: { value: string; label: string }[] };

/**
 * New Company License application: one category per application (D-014).
 * Renewal and amendment come with licenses (Phase 4B).
 */
export default function ApplicationCreate({ categories }: Props) {
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('New application')} />

            <div className="mx-auto max-w-xl space-y-6">
                <BackLink href={backTo()}>{t('Back to applications')}</BackLink>
                <h1 className="text-xl font-semibold">{t('New application')}</h1>
                <p className="text-sm text-muted-foreground">{t('Pest Control Products Company License')}</p>

                <Form {...store.form()} className="space-y-6">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="license_category">{t('License category')}</Label>
                                <NativeSelect id="license_category" name="license_category" defaultValue="" required>
                                    <option value="">{t('Choose…')}</option>
                                    {categories.map((category) => (
                                        <option key={category.value} value={category.value}>
                                            {t(category.label)}
                                        </option>
                                    ))}
                                </NativeSelect>
                                <InputError message={errors.license_category} />
                            </div>

                            <Button type="submit" disabled={processing}>
                                {processing && <Spinner />}
                                {t('Create draft')}
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </PortalLayout>
    );
}
