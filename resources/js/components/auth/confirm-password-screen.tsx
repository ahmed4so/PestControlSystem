import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { useArea } from '@/lib/area';
import { useTranslation } from '@/lib/i18n';

/**
 * Password re-confirmation before a sensitive action (03 §9, D-085).
 */
export default function ConfirmPasswordScreen() {
    const { t } = useTranslation();
    const { routes } = useArea();

    return (
        <AuthLayout
            title={t('Confirm your password')}
            description={t(
                'This is a secure area. Please confirm your password before continuing.',
            )}
        >
            <Head title={t('Confirm password')} />

            <Form
                {...routes.passwordConfirmStore.form()}
                resetOnSuccess={['password']}
            >
                {({ processing, errors }) => (
                    <div className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="password">{t('Password')}</Label>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                autoComplete="current-password"
                                autoFocus
                            />
                            <InputError message={errors.password} />
                        </div>

                        <Button
                            className="w-full"
                            disabled={processing}
                            data-test="confirm-password-button"
                        >
                            {processing && <Spinner />}
                            {t('Confirm password')}
                        </Button>
                    </div>
                )}
            </Form>
        </AuthLayout>
    );
}
