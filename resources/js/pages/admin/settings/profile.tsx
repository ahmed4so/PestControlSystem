import { Form, Head } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Admin/Settings/ProfileController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import LanguageSelect from '@/components/settings/language-select';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useTranslation } from '@/lib/i18n';
import { edit } from '@/routes/admin/profile';

type Props = {
    account: { name: string; email: string; phone: string | null; preferred_locale: string };
};

/**
 * A staff member's own details. The login email is changed by the System
 * Administrator (D-172).
 */
export default function Profile({ account }: Props) {
    const { t } = useTranslation();

    return (
        <AdminLayout breadcrumbs={[{ title: t('Profile'), href: edit().url }]}>
            <Head title={t('Profile')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <Heading variant="small" title={t('Profile information')} description={t('Update your name, phone and language')} />

                    <Form {...ProfileController.update.form()} options={{ preserveScroll: true }} className="space-y-6">
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">{t('Name')}</Label>
                                    <Input id="name" name="name" defaultValue={account.name} required autoComplete="name" />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">{t('Email address')}</Label>
                                    <Input id="email" type="email" value={account.email} readOnly disabled />
                                    <p className="text-xs text-muted-foreground">{t('Ask the System Administrator to change your login email.')}</p>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="phone">{t('Phone')}</Label>
                                    <Input id="phone" type="tel" name="phone" defaultValue={account.phone ?? ''} autoComplete="tel" />
                                    <InputError message={errors.phone} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="preferred_locale">{t('Preferred language')}</Label>
                                    <LanguageSelect defaultValue={account.preferred_locale} />
                                    <InputError message={errors.preferred_locale} />
                                </div>

                                <Button disabled={processing} data-test="update-profile-button">{t('Save')}</Button>
                            </>
                        )}
                    </Form>
                </div>
            </SettingsLayout>
        </AdminLayout>
    );
}
