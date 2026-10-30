import { Head } from '@inertiajs/react';
import TwoFactorPanel, { type TwoFactorPageProps } from '@/components/settings/two-factor-panel';
import PortalLayout from '@/layouts/portal-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useTranslation } from '@/lib/i18n';

export default function TwoFactor(props: TwoFactorPageProps) {
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Two-factor authentication')} />
            <SettingsLayout>
                <TwoFactorPanel {...props} />
            </SettingsLayout>
        </PortalLayout>
    );
}
