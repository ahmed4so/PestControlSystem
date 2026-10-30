import { Form, Head, usePage } from '@inertiajs/react';
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
 * Login form for either portal (03 §2). There is no "remember me" (D-084).
 */
export default function LoginScreen({
    title,
    description,
    status,
    footer,
}: {
    title: string;
    description: string;
    status?: string;
    footer?: React.ReactNode;
}) {
    const { t } = useTranslation();
    const { routes } = useArea();
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;

    return (
        <AuthLayout title={title} description={description}>
            <Head title={t('Log in')} />

            <div className="space-y-4">
                <StatusMessage status={status ?? flash?.success} />
                <StatusMessage status={flash?.error} tone="error" />

                <Form
                    {...routes.loginStore.form()}
                    resetOnSuccess={['password']}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="email">{t('Email address')}</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    autoComplete="username"
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center">
                                    <Label htmlFor="password">{t('Password')}</Label>
                                    <TextLink
                                        href={routes.passwordRequest()}
                                        className="ml-auto text-sm"
                                    >
                                        {t('Forgot password?')}
                                    </TextLink>
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autoComplete="current-password"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <Button
                                type="submit"
                                className="w-full"
                                size="lg"
                                disabled={processing}
                                data-test="login-button"
                            >
                                {processing && <Spinner />}
                                {t('Log in')}
                            </Button>
                        </>
                    )}
                </Form>

                {footer}
            </div>
        </AuthLayout>
    );
}
