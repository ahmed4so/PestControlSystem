import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import { index, show } from '@/routes/admin/companies';

type Props = {
    company: {
        id: number;
        name: string;
        business_registration_no: string;
        tax_compliance_no: string | null;
        email: string | null;
        phone: string | null;
        fms: string | null;
        region: string | null;
        address_district: string | null;
        address_town_or_market: string | null;
        address_street: string | null;
        address_details: string | null;
        account_email: string | null;
        complete: boolean;
        missing: string[];
        amendment_required_at: string | null;
    };
    persons: {
        id: number;
        full_name: string;
        national_id: string;
        nationality: string | null;
        email: string | null;
        phone: string | null;
        qualification: string | null;
        is_owner: boolean;
        released_at: string | null;
    }[];
    premises: {
        id: number;
        label: string;
        type: string;
        town: string | null;
        waste_disposal_method: string | null;
    }[];
    documents: {
        id: number;
        label: string;
        person: string | null;
        document_number: string | null;
        issued_on: string | null;
        expires_on: string | null;
        current: boolean;
        uploaded_at: string | null;
        url: string;
    }[];
};

/**
 * The company file, read-only (D-177). National IDs are masked (D-181);
 * opening a file is audit-logged (03 §13).
 */
export default function CompanyShow({ company, persons, premises, documents }: Props) {
    const { t } = useTranslation();
    const address = [company.address_details, company.address_street, company.address_town_or_market, company.address_district, company.region, company.fms]
        .filter(Boolean)
        .join(', ');

    return (
        <AdminLayout
            breadcrumbs={[
                { title: t('Companies'), href: index().url },
                { title: company.name, href: show(company.id).url },
            ]}
        >
            <Head title={company.name} />

            <div className="flex flex-col gap-6 p-4">
                <div className="flex flex-wrap items-center gap-2">
                    <h1 className="text-2xl font-bold tracking-tight">{company.name}</h1>
                    {company.complete ? <Badge variant="secondary">{t('Profile complete')}</Badge> : <Badge variant="outline">{t('Profile incomplete')}</Badge>}
                    {company.amendment_required_at && <Badge variant="destructive">{t('Amendment required')}</Badge>}
                </div>

                <section className="grid gap-1 text-sm">
                    <p><span className="text-muted-foreground">{t('Business registration number')}:</span> {company.business_registration_no}</p>
                    <p><span className="text-muted-foreground">{t('Tax compliance certificate number')}:</span> {company.tax_compliance_no ?? '—'}</p>
                    <p><span className="text-muted-foreground">{t('Company email')}:</span> {company.email ?? '—'}</p>
                    <p><span className="text-muted-foreground">{t('Company phone')}:</span> {company.phone ?? '—'}</p>
                    <p><span className="text-muted-foreground">{t('Address')}:</span> {address || '—'}</p>
                    <p><span className="text-muted-foreground">{t('Login email')}:</span> {company.account_email ?? '—'}</p>
                    {!company.complete && (
                        <p><span className="text-muted-foreground">{t('Still missing')}:</span> {company.missing.map((item) => t(item)).join(', ')}</p>
                    )}
                </section>

                <section className="space-y-2">
                    <h2 className="text-lg font-medium">{t('Persons')}</h2>
                    <ul className="divide-y rounded-md border text-sm">
                        {persons.map((person) => (
                            <li key={person.id} className="flex flex-wrap items-center gap-2 p-3">
                                <span className="font-medium">{person.full_name}</span>
                                <span className="text-muted-foreground">{person.national_id}</span>
                                {person.is_owner && <Badge>{t('Owner / Managing Director')}</Badge>}
                                {person.released_at && <Badge variant="destructive">{t('Released by SARIS')}</Badge>}
                                {person.qualification && <span className="text-muted-foreground">{person.qualification}</span>}
                            </li>
                        ))}
                    </ul>
                </section>

                <section className="space-y-2">
                    <h2 className="text-lg font-medium">{t('Premises')}</h2>
                    <ul className="divide-y rounded-md border text-sm">
                        {premises.map((item) => (
                            <li key={item.id} className="flex flex-wrap items-center gap-2 p-3">
                                <span className="font-medium">{item.label}</span>
                                <span className="text-muted-foreground">{t(item.type)}</span>
                                {item.town && <span className="text-muted-foreground">{item.town}</span>}
                                {item.waste_disposal_method && <span>{item.waste_disposal_method}</span>}
                            </li>
                        ))}
                    </ul>
                </section>

                <section className="space-y-2">
                    <h2 className="text-lg font-medium">{t('Documents')}</h2>
                    <ul className="divide-y rounded-md border text-sm">
                        {documents.map((document) => (
                            <li key={document.id} className="flex flex-wrap items-center gap-2 p-3">
                                <a href={document.url} className="font-medium underline">
                                    {t(document.label)}
                                </a>
                                {document.person && <span className="text-muted-foreground">{document.person}</span>}
                                {document.document_number && <span>{document.document_number}</span>}
                                {document.expires_on && <span className="text-muted-foreground">{t('expires on :date', { date: document.expires_on })}</span>}
                                {!document.current && <Badge variant="outline">{t('Replaced')}</Badge>}
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        </AdminLayout>
    );
}
