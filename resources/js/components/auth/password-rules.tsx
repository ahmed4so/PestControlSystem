import { useTranslation } from '@/lib/i18n';

/**
 * The password policy, shown under new-password fields (03 §6, D-082).
 */
export default function PasswordRules() {
    const { t } = useTranslation();

    return (
        <p className="text-xs text-muted-foreground">
            {t(
                'At least 12 characters, with upper and lower case letters, a number and a symbol. Do not reuse one of your last 5 passwords.',
            )}
        </p>
    );
}
