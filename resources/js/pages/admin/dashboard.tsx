import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import { dashboard } from '@/routes/admin';

export default function Dashboard() {
    const { t } = useTranslation();
    const { flash } = usePage<{ flash: { success?: string } }>().props;

    return (
        <AdminLayout breadcrumbs={[{ title: t('Dashboard'), href: dashboard().url }]}>
            <Head title={t('Dashboard')} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {flash?.success && <p className="text-sm text-green-700">{flash.success}</p>}
                <h1 className="text-2xl font-semibold">{t('Dashboard')}</h1>
            </div>
        </AdminLayout>
    );
}
