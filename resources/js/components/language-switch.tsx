import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/lib/i18n';
import { update } from '@/routes/locale';

/**
 * English / Somali switch (D-032, D-169).
 */
export default function LanguageSwitch({ className }: { className?: string }) {
    const { locale } = useTranslation();
    const other = locale === 'so' ? 'en' : 'so';

    return (
        <Button
            type="button"
            variant="ghost"
            size="sm"
            className={className}
            onClick={() =>
                router.post(update.url(other), {}, { preserveScroll: true })
            }
        >
            {other === 'so' ? 'Soomaali' : 'English'}
        </Button>
    );
}
