import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import { queues } from '@/routes/admin';
import { show } from '@/routes/admin/applications';

type Row = {
    id: number;
    reference: string | null;
    company: string;
    service: string;
    category: string | null;
    status: string;
    stage: string;
    days: number;
    flags?: string[];
};
type Paginated = { data: Row[]; links: { url: string | null; label: string; active: boolean }[] };
type Stage = { status: string; stage: string; role: string; count: number; oldest_days: number };

const TAB_LABELS: Record<string, string> = {
    mine: 'My work',
    stages: 'By stage',
    waiting: 'Waiting on company',
    flags: 'Flags',
};

/**
 * Staff work queues (04 §15, D-105): my work, by stage, waiting on the
 * company and flags.
 */
export default function Queues({ tab, tabs, items, stages }: { tab: string; tabs: string[]; items: Paginated | null; stages: Stage[] }) {
    const { t } = useTranslation();

    return (
        <AdminLayout breadcrumbs={[{ title: t('Work queues'), href: queues().url }]}>
            <Head title={t('Work queues')} />

            <div className="flex flex-col gap-4 p-4">
                <h1 className="text-2xl font-bold tracking-tight">{t('Work queues')}</h1>

                <nav className="flex flex-wrap gap-2">
                    {tabs.map((name) => (
                        <Link
                            key={name}
                            href={queues({ query: { tab: name } })}
                            className={`rounded-md border px-3 py-1 text-sm ${name === tab ? 'bg-muted font-medium' : ''}`}
                        >
                            {t(TAB_LABELS[name])}
                        </Link>
                    ))}
                </nav>

                {tab === 'stages' ? (
                    stages.length === 0 ? (
                        <p className="text-muted-foreground">{t('Nothing is waiting.')}</p>
                    ) : (
                        <ul className="divide-y rounded-md border">
                            {stages.map((stage) => (
                                <li key={stage.status} className="flex flex-wrap items-center gap-2 p-3">
                                    <span className="font-medium">{stage.stage}</span>
                                    <Badge variant="secondary">{stage.count}</Badge>
                                    <span className="ml-auto text-sm text-muted-foreground">
                                        {t('Oldest: :days days', { days: String(stage.oldest_days) })}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )
                ) : items && items.data.length > 0 ? (
                    <>
                        <ul className="divide-y rounded-md border">
                            {items.data.map((row) => (
                                <li key={row.id}>
                                    <Link href={show(row.id)} className="flex flex-wrap items-center gap-2 p-3 hover:bg-muted">
                                        <span className="font-medium">{row.reference}</span>
                                        <span className="text-sm">{row.company}</span>
                                        <span className="text-sm text-muted-foreground">
                                            {t(row.service)}
                                            {row.category && ` — ${t(row.category)}`}
                                        </span>
                                        <Badge variant="outline">{row.stage}</Badge>
                                        {row.flags?.map((flag) => (
                                            <Badge key={flag} variant="destructive">
                                                {t(flag)}
                                            </Badge>
                                        ))}
                                        <span className="ml-auto text-sm text-muted-foreground">{t(':days days', { days: String(row.days) })}</span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                        <div className="flex flex-wrap gap-1">
                            {items.links.map((link, i) =>
                                link.url ? (
                                    <Link key={i} href={link.url} className={`rounded px-2 py-1 text-sm ${link.active ? 'bg-muted font-medium' : ''}`} preserveState>
                                        {link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                                    </Link>
                                ) : null,
                            )}
                        </div>
                    </>
                ) : (
                    <p className="text-muted-foreground">{t('Nothing is waiting.')}</p>
                )}
            </div>
        </AdminLayout>
    );
}
