import { Head } from '@inertiajs/react';
import PasswordForm, {
    type PasswordPageProps,
} from '@/components/settings/password-form';
import AdminLayout from '@/layouts/admin-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useTranslation } from '@/lib/i18n';
import { edit } from '@/routes/admin/user-password';

export default function Password(props: PasswordPageProps) {
    const { t } = useTranslation();

    return (
        <AdminLayout breadcrumbs={[{ title: t('Password'), href: edit().url }]}>
            <Head title={t('Password')} />
            <SettingsLayout>
                <PasswordForm {...props} />
            </SettingsLayout>
        </AdminLayout>
    );
}
