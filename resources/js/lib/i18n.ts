import { usePage } from '@inertiajs/react';

export type Translations = Record<string, string>;

export type Replacements = Record<string, string | number>;

/**
 * Translate UI text (D-054). The key is the English text (D-116); a missing
 * key falls back to the key itself. Placeholders use Laravel's `:name` style.
 */
export function translate(
    translations: Translations,
    key: string,
    replacements: Replacements = {},
): string {
    let line = translations[key] ?? key;

    for (const [name, value] of Object.entries(replacements)) {
        line = line.replaceAll(`:${name}`, String(value));
    }

    return line;
}

/**
 * Returns t() bound to the translations shared through Inertia.
 */
export function useTranslation() {
    const { translations, locale } = usePage().props;

    return {
        locale,
        t: (key: string, replacements?: Replacements) =>
            translate(translations, key, replacements),
    };
}
