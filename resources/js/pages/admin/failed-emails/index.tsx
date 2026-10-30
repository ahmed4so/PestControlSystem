import { Head } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import { index } from '@/routes/admin/failed-emails';

type Log = { id: number; email: string; template: string; error: string | null; created_at: string | null };

/**
 * Emails that failed after 3 tries (D-129).
 */
export default function FailedEmails({ logs }: { logs: { data: Log[] } }) {
    const { t } = useTranslation();

    return (
        <AdminLayout breadcrumbs={[{ title: t('Failed emails'), href: index().url }]}>
            <Head title={t('Failed emails')} />

            <div className="flex flex-col gap-4 p-4">
                <h1 className="text-2xl font-bold tracking-tight">{t('Failed emails')}</h1>

                {logs.data.length === 0 ? (
                    <p className="text-muted-foreground">{t('No failed emails.')}</p>
                ) : (
                    <div className="overflow-x-auto rounded-md border">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-muted">
                                <tr>
                                    <th className="p-2">{t('Date')}</th>
                                    <th className="p-2">{t('Email address')}</th>
                                    <th className="p-2">{t('Email')}</th>
                                    <th className="p-2">{t('Error')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {logs.data.map((log) => (
                                    <tr key={log.id} className="border-t">
                                        <td className="p-2 whitespace-nowrap">{log.created_at}</td>
                                        <td className="p-2">{log.email}</td>
                                        <td className="p-2 font-mono text-xs">{log.template}</td>
                                        <td className="p-2 text-xs">{log.error}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
