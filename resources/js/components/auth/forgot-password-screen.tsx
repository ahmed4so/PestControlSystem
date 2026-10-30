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
import { useArea } from '@/lib/area';
import { useTranslation } from '@/lib/i18n';

/**
 * Request a password reset link. The answer is the same whether or not the
 * email has an account (03 §4).
 */
export default function ForgotPasswordScreen({
    status,
    honeypot,
}: {
    status?: string;
    honeypot: Honeypot;
}) {
    const { t } = useTranslation();
    const { routes } = useArea();

    return (
        <AuthLayout
            title={t('Forgot password')}
            description={t('Enter your email to receive a password reset link')}
        >
            <Head title={t('Forgot password')} />

            <div className="space-y-6">
                <StatusMessage status={status} />

                <Form {...routes.passwordEmail.form()} className="space-y-4">
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
                                    autoComplete="email"
                                    autoFocus
                                    required
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <Button className="w-full" disabled={processing}>
                                {processing && <Spinner />}
                                {t('Email password reset link')}
                            </Button>
                        </>
                    )}
                </Form>

                <div className="text-center text-sm text-muted-foreground">
                    <TextLink href={routes.login()}>
                        {t('Back to log in')}
                    </TextLink>
                </div>
            </div>
        </AuthLayout>
    );
}
