import { Head } from '@inertiajs/react';
import DocumentUpload from '@/components/company/document-upload';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';

type DocumentRow = {
    id: number;
    document_number: string | null;
    issued_on: string | null;
    expires_on: string | null;
    uploaded_at: string | null;
    url: string;
};

type DocumentType = {
    value: string;
    label: string;
    isCertificate: boolean;
    canUpload: boolean;
    current: DocumentRow | null;
    history: DocumentRow[];
};

/**
 * Company documents (05 §4.4): the current document of each type, its
 * history, and a new upload that replaces it (D-067).
 */
export default function Documents({ types }: { types: DocumentType[] }) {
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Documents')} />

            <div className="mx-auto max-w-3xl space-y-6">
                <h1 className="text-xl font-semibold">{t('Documents')}</h1>

                {types.map((type) => (
                    <section key={type.value} className="space-y-3 rounded-md border p-4">
                        <h2 className="font-medium">{t(type.label)}</h2>

                        {type.current ? (
                            <p className="text-sm">
                                <a href={type.current.url} className="underline">
                                    {t('Current document')}
                                </a>
                                {type.current.document_number && <> · {type.current.document_number}</>}
                                {type.current.expires_on && <> · {t('expires on :date', { date: type.current.expires_on })}</>}
                            </p>
                        ) : (
                            <p className="text-sm text-muted-foreground">{t('Not uploaded yet.')}</p>
                        )}

                        {type.canUpload ? (
                            <DocumentUpload type={type.value} isCertificate={type.isCertificate} />
                        ) : (
                            <p className="text-sm text-muted-foreground">{t('Locked while an application is being processed.')}</p>
                        )}

                        {type.history.length > 0 && (
                            <details className="text-sm">
                                <summary className="cursor-pointer text-muted-foreground">{t('Earlier versions')}</summary>
                                <ul className="mt-2 space-y-1">
                                    {type.history.map((document) => (
                                        <li key={document.id}>
                                            <a href={document.url} className="underline">
                                                {document.uploaded_at?.slice(0, 10)}
                                            </a>
                                            {document.document_number && <> · {document.document_number}</>}
                                        </li>
                                    ))}
                                </ul>
                            </details>
                        )}
                    </section>
                ))}
            </div>
        </PortalLayout>
    );
}
