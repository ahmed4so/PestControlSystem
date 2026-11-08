import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';
import { create, show } from '@/routes/portal/applications';

type Item = {
    id: number;
    reference: string | null;
    service: string;
    category: string | null;
    status: string;
    updated_at: string | null;
};

/**
 * The company's applications, with the status a company sees (D-045).
 */
export default function ApplicationsIndex({ applications }: { applications: Item[] }) {
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Applications')} />

            <div className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h1 className="text-xl font-semibold">{t('Applications')}</h1>
                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            {t('New application')}
                        </Link>
                    </Button>
                </div>

                {applications.length === 0 ? (
                    <p className="text-muted-foreground">{t('You have no applications yet.')}</p>
                ) : (
                    <ul className="divide-y rounded-md border">
                        {applications.map((item) => (
                            <li key={item.id}>
                                <Link href={show(item.id)} className="flex flex-wrap items-center gap-2 p-3 hover:bg-muted">
                                    <span className="font-medium">{item.reference ?? t('Draft')}</span>
                                    <span className="text-sm text-muted-foreground">
                                        {t(item.service)}
                                        {item.category && ` — ${t(item.category)}`}
                                    </span>
                                    <Badge variant="secondary" className="ml-auto">
                                        {t(item.status)}
                                    </Badge>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </PortalLayout>
    );
}
