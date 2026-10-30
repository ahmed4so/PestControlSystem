import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';
import { create, edit } from '@/routes/portal/premises';

type Item = { id: number; label: string; type: string; available_count: number };

/**
 * The company's premises (02 §4.4).
 */
export default function PremisesIndex({ premises, itemCount, canCreate }: { premises: Item[]; itemCount: number; canCreate: boolean }) {
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Premises')} />

            <div className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h1 className="text-xl font-semibold">{t('Premises')}</h1>
                    {canCreate && (
                        <Button asChild>
                            <Link href={create()}>
                                <Plus />
                                {t('Add premises')}
                            </Link>
                        </Button>
                    )}
                </div>

                {premises.length === 0 ? (
                    <p className="text-muted-foreground">{t('Add your warehouses, shops, stores and offices.')}</p>
                ) : (
                    <ul className="divide-y rounded-md border">
                        {premises.map((item) => (
                            <li key={item.id}>
                                <Link href={edit(item.id)} className="flex flex-wrap items-center gap-2 p-3 hover:bg-muted">
                                    <span className="font-medium">{item.label}</span>
                                    <span className="text-sm text-muted-foreground">{t(item.type)}</span>
                                    <span className="ml-auto text-sm text-muted-foreground">
                                        {t(':done of :total checklist items available', { done: item.available_count, total: itemCount })}
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </PortalLayout>
    );
}
