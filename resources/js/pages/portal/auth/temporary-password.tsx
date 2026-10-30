import { Form, Head } from '@inertiajs/react';
import HoneypotFields, {
    type Honeypot,
} from '@/components/auth/honeypot-fields';
import StatusMessage from '@/components/auth/status-message';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { useTranslation } from '@/lib/i18n';
import { login } from '@/routes/portal';
import { store } from '@/routes/portal/temporary-password';

/**
 * "Send me a new temporary password" for accounts that have not logged in
 * yet (03 §4).
 */
export default function TemporaryPassword({
    status,
    honeypot,
}: {
    status?: string;
    honeypot: Honeypot;
}) {
    const { t } = useTranslation();

    return (
        <AuthLayout
            title={t('Send me a new temporary password')}
            description={t(
                'For accounts that have not logged in yet. The new password is valid for 1 hour.',
            )}
        >
            <Head title={t('Send me a new temporary password')} />

            <div className="space-y-6">
                <StatusMessage status={status} />

                <Form {...store.form()} className="space-y-4">
                    {({ processing, errors }) => (
                        <>
                            <HoneypotFields honeypot={honeypot} />
                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t('Email address')}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    autoComplete="email"
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                            >
                                {processing && <Spinner />}
                                {t('Send')}
                            </Button>
                        </>
                    )}
                </Form>

                <p className="text-center text-sm text-muted-foreground">
                    <TextLink href={login()}>{t('Back to log in')}</TextLink>
                </p>
            </div>
        </AuthLayout>
    );
}
