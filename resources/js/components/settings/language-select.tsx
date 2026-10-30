import { useTranslation } from '@/lib/i18n';

/**
 * Preferred language: the UI language and the order of email languages
 * (D-032, D-125).
 */
export default function LanguageSelect({
    defaultValue,
}: {
    defaultValue: string;
}) {
    const { t } = useTranslation();

    return (
        <select
            id="preferred_locale"
            name="preferred_locale"
            defaultValue={defaultValue}
            className="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
        >
            <option value="en">{t('English')}</option>
            <option value="so">{t('Somali')}</option>
        </select>
    );
}
