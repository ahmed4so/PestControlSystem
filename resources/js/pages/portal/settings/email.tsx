import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PortalLayout from '@/layouts/portal-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useTranslation } from '@/lib/i18n';
import { store } from '@/routes/portal/email';

/**
 * Change the login email (D-093): a link goes to the new address, a notice
 * to the old one, and the email changes only when the link is used.
 */
export default function LoginEmail({
    email,
    pendingEmail,
}: {
    email: string;
    pendingEmail: string | null;
}) {
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Login email')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title={t('Change login email')}
                        description={t('Your current login email is :email', {
                            email,
                        })}
                    />

                    {pendingEmail && (
                        <p className="text-sm text-muted-foreground">
                            {t(
                                'Waiting for confirmation of :email. Open the link we sent to that address.',
                                { email: pendingEmail },
                            )}
                        </p>
                    )}

                    <Form
                        {...store.form()}
                        resetOnSuccess
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="email">
                                        {t('New login email')}
                                    </Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autoComplete="email"
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <Button disabled={processing}>
                                    {t('Send confirmation link')}
                                </Button>
                            </>
                        )}
                    </Form>
                </div>
            </SettingsLayout>
        </PortalLayout>
    );
}
