import { Head } from '@inertiajs/react';
import PasswordForm, {
    type PasswordPageProps,
} from '@/components/settings/password-form';
import PortalLayout from '@/layouts/portal-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useTranslation } from '@/lib/i18n';

export default function Password(props: PasswordPageProps) {
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Password')} />
            <SettingsLayout>
                <PasswordForm {...props} />
            </SettingsLayout>
        </PortalLayout>
    );
}
