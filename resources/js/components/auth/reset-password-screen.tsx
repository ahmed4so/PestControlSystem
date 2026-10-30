import { Form, Head } from '@inertiajs/react';
import PasswordRules from '@/components/auth/password-rules';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { useArea } from '@/lib/area';
import { useTranslation } from '@/lib/i18n';

export default function ResetPasswordScreen({
    token,
    email,
}: {
    token: string;
    email: string;
}) {
    const { t } = useTranslation();
    const { routes } = useArea();

    return (
        <AuthLayout
            title={t('Reset password')}
            description={t('Please enter your new password below')}
        >
            <Head title={t('Reset password')} />

            <Form
                {...routes.passwordUpdate.form()}
                transform={(data) => ({ ...data, token, email })}
                resetOnSuccess={['password', 'password_confirmation']}
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="email">{t('Email address')}</Label>
                            <Input id="email" type="email" name="email" value={email} readOnly />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">{t('New password')}</Label>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                autoComplete="new-password"
                                autoFocus
                            />
                            <PasswordRules />
                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">{t('Confirm password')}</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                autoComplete="new-password"
                            />
                            <InputError message={errors.password_confirmation} />
                        </div>

                        <Button type="submit" className="w-full" disabled={processing}>
                            {processing && <Spinner />}
                            {t('Reset password')}
                        </Button>
                    </div>
                )}
            </Form>
        </AuthLayout>
    );
}
