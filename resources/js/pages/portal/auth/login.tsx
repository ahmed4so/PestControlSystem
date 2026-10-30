import LoginScreen from '@/components/auth/login-screen';
import TextLink from '@/components/text-link';
import { useTranslation } from '@/lib/i18n';
import { signup, temporaryPassword } from '@/routes/portal';

/**
 * Company login (/login). Staff accounts cannot log in here (D-048).
 */
export default function Login({ status }: { status?: string }) {
    const { t } = useTranslation();

    return (
        <LoginScreen
            title={t('Company Portal login')}
            description={t('Log in to apply for SARIS licenses and permits')}
            status={status}
            footer={
                <div className="space-y-2 text-center text-sm text-muted-foreground">
                    <p>
                        {t('New to SARIS?')}{' '}
                        <TextLink href={signup()}>
                            {t('Create an account')}
                        </TextLink>
                    </p>
                    <p>
                        <TextLink href={temporaryPassword()}>
                            {t('Send me a new temporary password')}
                        </TextLink>
                    </p>
                </div>
            }
        />
    );
}
