import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import LanguageSelect from '@/components/settings/language-select';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PortalLayout from '@/layouts/portal-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useTranslation } from '@/lib/i18n';
import { edit as editEmail } from '@/routes/portal/email';
import { update } from '@/routes/portal/profile';

type Props = {
    account: {
        full_name: string;
        email: string;
        phone: string;
        preferred_locale: string;
        pending_email: string | null;
    };
};

export default function Profile({ account }: Props) {
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Profile')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title={t('Your details')}
                        description={t('Update your name, phone and language')}
                    />

                    <Form
                        {...update.form()}
                        options={{ preserveScroll: true }}
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="full_name">
                                        {t('Full name')}
                                    </Label>
                                    <Input
                                        id="full_name"
                                        name="full_name"
                                        defaultValue={account.full_name}
                                        required
                                        autoComplete="name"
                                    />
                                    <InputError message={errors.full_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">
                                        {t('Login email')}
                                    </Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={account.email}
                                        readOnly
                                        disabled
                                    />
                                    <TextLink
                                        href={editEmail()}
                                        className="text-sm"
                                    >
                                        {t('Change login email')}
                                    </TextLink>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="phone">{t('Phone')}</Label>
                                    <Input
                                        id="phone"
                                        type="tel"
                                        name="phone"
                                        defaultValue={account.phone}
                                        required
                                        autoComplete="tel"
                                    />
                                    <InputError message={errors.phone} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="preferred_locale">
                                        {t('Preferred language')}
                                    </Label>
                                    <LanguageSelect
                                        defaultValue={account.preferred_locale}
                                    />
                                    <InputError
                                        message={errors.preferred_locale}
                                    />
                                </div>

                                <Button disabled={processing}>
                                    {t('Save')}
                                </Button>
                            </>
                        )}
                    </Form>
                </div>
            </SettingsLayout>
        </PortalLayout>
    );
}
