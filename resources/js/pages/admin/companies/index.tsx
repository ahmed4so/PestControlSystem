import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import { index, show } from '@/routes/admin/companies';

type Row = { id: number; name: string; business_registration_no: string; complete: boolean; amendment_required: boolean };
type Paginated = { data: Row[]; links: { url: string | null; label: string; active: boolean }[] };

/**
 * Read-only company directory for staff (D-177).
 */
export default function CompaniesIndex({ companies, search }: { companies: Paginated; search: string }) {
    const { t } = useTranslation();
    const [query, setQuery] = useState(search);

    return (
        <AdminLayout breadcrumbs={[{ title: t('Companies'), href: index().url }]}>
            <Head title={t('Companies')} />

            <div className="flex flex-col gap-4 p-4">
                <h1 className="text-2xl font-bold tracking-tight">{t('Companies')}</h1>

                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        router.get(index.url(), { search: query }, { preserveState: true });
                    }}
                >
                    <Input
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                        placeholder={t('Search by name or registration number')}
                        className="max-w-md"
                    />
                </form>

                {companies.data.length === 0 ? (
                    <p className="text-muted-foreground">{t('No companies found.')}</p>
                ) : (
                    <ul className="divide-y rounded-md border">
                        {companies.data.map((company) => (
                            <li key={company.id}>
                                <Link href={show(company.id)} className="flex flex-wrap items-center gap-2 p-3 hover:bg-muted">
                                    <span className="font-medium">{company.name}</span>
                                    <span className="text-sm text-muted-foreground">{company.business_registration_no}</span>
                                    {company.complete ? <Badge variant="secondary">{t('Profile complete')}</Badge> : <Badge variant="outline">{t('Profile incomplete')}</Badge>}
                                    {company.amendment_required && <Badge variant="destructive">{t('Amendment required')}</Badge>}
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}

                <div className="flex flex-wrap gap-1">
                    {companies.links.map((link, i) =>
                        link.url ? (
                            <Link key={i} href={link.url} className={`rounded px-2 py-1 text-sm ${link.active ? 'bg-muted font-medium' : ''}`} preserveState>
                                {link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                            </Link>
                        ) : null,
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
