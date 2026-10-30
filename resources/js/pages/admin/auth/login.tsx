import LoginScreen from '@/components/auth/login-screen';
import { useTranslation } from '@/lib/i18n';

/**
 * Staff login (/admin/login). Company accounts cannot log in here (D-048).
 */
export default function Login({ status }: { status?: string }) {
    const { t } = useTranslation();

    return (
        <LoginScreen
            title={t('SARIS staff login')}
            description={t('Admin Portal for SARIS staff')}
            status={status}
        />
    );
}
