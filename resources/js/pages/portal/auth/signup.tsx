import { Form, Head } from '@inertiajs/react';
import HoneypotFields, {
    type Honeypot,
} from '@/components/auth/honeypot-fields';
import StatusMessage from '@/components/auth/status-message';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { useTranslation } from '@/lib/i18n';
import { login } from '@/routes/portal';
import { store } from '@/routes/portal/signup';

/**
 * Company sign-up (03 §4, 05 §3.1, D-063): full name, email, phone and
 * consent. A temporary password is emailed; the answer is the same for
 * every email.
 */
export default function SignUp({
    status,
    honeypot,
}: {
    status?: string;
    honeypot: Honeypot;
}) {
    const { t } = useTranslation();

    return (
        <AuthLayout
            title={t('Create a company account')}
            description={t('We will email you a temporary password to log in.')}
        >
            <Head title={t('Create a company account')} />

            <div className="space-y-6">
                <StatusMessage status={status} />

                <Form {...store.form()} resetOnSuccess className="space-y-4">
                    {({ processing, errors }) => (
                        <>
                            <HoneypotFields honeypot={honeypot} />

                            <div className="grid gap-2">
                                <Label htmlFor="full_name">
                                    {t('Full name')}
                                </Label>
                                <Input
                                    id="full_name"
                                    name="full_name"
                                    required
                                    autoFocus
                                    autoComplete="name"
                                    maxLength={150}
                                />
                                <InputError message={errors.full_name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t('Email address')}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoComplete="email"
                                    maxLength={190}
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="phone">{t('Phone')}</Label>
                                <Input
                                    id="phone"
                                    type="tel"
                                    name="phone"
                                    required
                                    autoComplete="tel"
                                    placeholder="+252 61 234 5678"
                                />
                                <InputError message={errors.phone} />
                            </div>

                            <div className="flex items-start gap-3">
                                <Checkbox
                                    id="consent"
                                    name="consent"
                                    value="1"
                                    required
                                />
                                <Label
                                    htmlFor="consent"
                                    className="text-sm leading-snug font-normal"
                                >
                                    {t(
                                        'I agree that SARIS stores and uses my details to process my applications, as described in the privacy notice.',
                                    )}
                                </Label>
                            </div>
                            <InputError message={errors.consent} />

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                            >
                                {processing && <Spinner />}
                                {t('Create account')}
                            </Button>
                        </>
                    )}
                </Form>

                <p className="text-center text-sm text-muted-foreground">
                    {t('Already have an account?')}{' '}
                    <TextLink href={login()}>{t('Log in')}</TextLink>
                </p>
            </div>
        </AuthLayout>
    );
}
