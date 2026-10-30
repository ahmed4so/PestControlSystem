import { Head } from '@inertiajs/react';
import TwoFactorPanel, {
    type TwoFactorPageProps,
} from '@/components/settings/two-factor-panel';
import AdminLayout from '@/layouts/admin-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useTranslation } from '@/lib/i18n';
import { show } from '@/routes/admin/two-factor';

export default function TwoFactor(props: TwoFactorPageProps) {
    const { t } = useTranslation();

    return (
        <AdminLayout
            breadcrumbs={[
                { title: t('Two-factor authentication'), href: show.url() },
            ]}
        >
            <Head title={t('Two-factor authentication')} />
            <SettingsLayout>
                <TwoFactorPanel {...props} />
            </SettingsLayout>
        </AdminLayout>
    );
}
