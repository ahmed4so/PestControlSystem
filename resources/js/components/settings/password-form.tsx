import { Transition } from '@headlessui/react';
import { Form } from '@inertiajs/react';
import { useRef } from 'react';
import PasswordRules from '@/components/auth/password-rules';
import StatusMessage from '@/components/auth/status-message';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useArea } from '@/lib/area';
import { useTranslation } from '@/lib/i18n';

export type PasswordPageProps = {
    mustChange: boolean;
    firstLogin: boolean;
    expiresAt: string | null;
};

/**
 * Change password for either portal. On first login and after the 90-day
 * expiry the account must set a new password before anything else (D-082).
 */
export default function PasswordForm({
    mustChange,
    firstLogin,
}: PasswordPageProps) {
    const { t } = useTranslation();
    const { routes } = useArea();
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    return (
        <div className="space-y-6">
            <Heading
                variant="small"
                title={t('Update password')}
                description={t(
                    'Use a long password that you do not use anywhere else',
                )}
            />

            {mustChange && (
                <StatusMessage
                    tone="error"
                    status={
                        firstLogin
                            ? t(
                                  'Choose your own password to continue. Enter the temporary password from the email as the current password.',
                              )
                            : t(
                                  'Your password has expired. Choose a new password to continue.',
                              )
                    }
                />
            )}

            <Form
                {...routes.userPasswordUpdate.form()}
                options={{ preserveScroll: true }}
                resetOnError={[
                    'password',
                    'password_confirmation',
                    'current_password',
                ]}
                resetOnSuccess
                onError={(errors) => {
                    if (errors.password) {
                        passwordInput.current?.focus();
                    }

                    if (errors.current_password) {
                        currentPasswordInput.current?.focus();
                    }
                }}
                className="space-y-6"
            >
                {({ errors, processing, recentlySuccessful }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="current_password">
                                {firstLogin
                                    ? t('Temporary password')
                                    : t('Current password')}
                            </Label>
                            <Input
                                id="current_password"
                                ref={currentPasswordInput}
                                name="current_password"
                                type="password"
                                autoComplete="current-password"
                            />
                            <InputError message={errors.current_password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">
                                {t('New password')}
                            </Label>
                            <Input
                                id="password"
                                ref={passwordInput}
                                name="password"
                                type="password"
                                autoComplete="new-password"
                            />
                            <PasswordRules />
                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">
                                {t('Confirm password')}
                            </Label>
                            <Input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autoComplete="new-password"
                            />
                            <InputError
                                message={errors.password_confirmation}
                            />
                        </div>

                        <div className="flex items-center gap-4">
                            <Button
                                disabled={processing}
                                data-test="update-password-button"
                            >
                                {t('Save password')}
                            </Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">
                                    {t('Saved')}
                                </p>
                            </Transition>
                        </div>
                    </>
                )}
            </Form>
        </div>
    );
}
