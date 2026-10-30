import { Form } from '@inertiajs/react';
import { ShieldBan, ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import StatusMessage from '@/components/auth/status-message';
import Heading from '@/components/heading';
import TwoFactorRecoveryCodes from '@/components/two-factor-recovery-codes';
import TwoFactorSetupModal from '@/components/two-factor-setup-modal';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/hooks/use-two-factor-auth';
import { useArea } from '@/lib/area';
import { useTranslation } from '@/lib/i18n';

export type TwoFactorPageProps = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
    canDisable?: boolean;
    status?: string;
};

/**
 * 2FA settings for either portal. Mandatory for staff, who cannot disable
 * it; optional for companies (03 §8).
 */
export default function TwoFactorPanel({
    requiresConfirmation = false,
    twoFactorEnabled = false,
    canDisable = false,
    status,
}: TwoFactorPageProps) {
    const { t } = useTranslation();
    const { routes } = useArea();
    const {
        qrCodeSvg,
        hasSetupData,
        manualSetupKey,
        clearSetupData,
        fetchSetupData,
        recoveryCodesList,
        fetchRecoveryCodes,
        errors,
    } = useTwoFactorAuth();
    const [showSetupModal, setShowSetupModal] = useState<boolean>(false);

    return (
        <div className="space-y-6">
            <Heading
                variant="small"
                title={t('Two-factor authentication')}
                description={t('Manage your two-factor authentication settings')}
            />
            <StatusMessage status={status} tone="error" />
            {twoFactorEnabled ? (
                <div className="flex flex-col items-start justify-start space-y-4">
                    <Badge variant="default">{t('Enabled')}</Badge>
                    <p className="text-muted-foreground">
                            {t('With two-factor authentication enabled, you will be asked for a code from the authenticator app on your phone when you log in.')}
                        </p>

                    <TwoFactorRecoveryCodes
                        recoveryCodesList={recoveryCodesList}
                        fetchRecoveryCodes={fetchRecoveryCodes}
                        errors={errors}
                    />

                    {canDisable && routes.twoFactorDisable && (
                            <div className="relative inline">
                                <Form {...routes.twoFactorDisable.form()}>
                            {({ processing }) => (
                                <Button
                                    variant="destructive"
                                    type="submit"
                                    disabled={processing}
                                >
                                    <ShieldBan /> {t('Disable 2FA')}
                                </Button>
                            )}
                        </Form>
                            </div>
                        )}
                </div>
            ) : (
                <div className="flex flex-col items-start justify-start space-y-4">
                    <Badge variant="destructive">{t('Disabled')}</Badge>
                    <p className="text-muted-foreground">
                            {t('When you enable two-factor authentication, you will be asked for a code from an authenticator app on your phone when you log in.')}
                        </p>

                    <div>
                        {hasSetupData ? (
                            <Button
                                onClick={() => setShowSetupModal(true)}
                            >
                                <ShieldCheck />
                                {t('Continue setup')}
                            </Button>
                        ) : (
                            <Form
                                {...routes.twoFactorEnable.form()}
                                onSuccess={() =>
                                    setShowSetupModal(true)
                                }
                            >
                                {({ processing }) => (
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                    >
                                        <ShieldCheck />
                                        {t('Enable 2FA')}
                                    </Button>
                                )}
                            </Form>
                        )}
                    </div>
                </div>
            )}

            <TwoFactorSetupModal
                isOpen={showSetupModal}
                onClose={() => setShowSetupModal(false)}
                requiresConfirmation={requiresConfirmation}
                twoFactorEnabled={twoFactorEnabled}
                qrCodeSvg={qrCodeSvg}
                manualSetupKey={manualSetupKey}
                clearSetupData={clearSetupData}
                fetchSetupData={fetchSetupData}
                errors={errors}
            />
        </div>
    );
}
