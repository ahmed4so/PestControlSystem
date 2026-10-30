import { Form, Head, usePage } from '@inertiajs/react';
import StatusMessage from '@/components/auth/status-message';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import { find, release, show } from '@/routes/admin/national-ids';

type Match = {
    full_name: string;
    national_id: string;
    company: string;
    business_registration_no: string;
    is_owner: boolean;
};

/**
 * National-ID release by the System Administrator (D-066, D-095): find the
 * person by the full ID, then release it with a reason.
 */
export default function NationalIdRelease({ match, searched = false, nationalId = '' }: { match: Match | null; searched?: boolean; nationalId?: string }) {
    const { t } = useTranslation();
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;

    return (
        <AdminLayout breadcrumbs={[{ title: t('National-ID release'), href: show().url }]}>
            <Head title={t('National-ID release')} />

            <div className="flex max-w-2xl flex-col gap-6 p-4">
                <div className="space-y-1">
                    <h1 className="text-2xl font-bold tracking-tight">{t('National-ID release')}</h1>
                    <p className="text-sm text-muted-foreground">
                        {t('Release a national ID from its company after verifying that the person has moved. The company is notified and must submit an amendment.')}
                    </p>
                </div>

                <StatusMessage status={flash?.success} />
                <StatusMessage status={flash?.error} tone="error" />

                <Form {...find.form()} className="flex flex-wrap items-end gap-2">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="search_national_id_no">{t('National ID number (11 digits)')}</Label>
                                <Input id="search_national_id_no" name="national_id_no" inputMode="numeric" maxLength={11} defaultValue={nationalId} required autoComplete="off" />
                                <InputError message={errors.national_id_no} />
                            </div>
                            <Button type="submit" variant="secondary" disabled={processing}>
                                {t('Find')}
                            </Button>
                        </>
                    )}
                </Form>

                {searched && !match && <p className="text-sm text-muted-foreground">{t('No active person has this national ID.')}</p>}

                {match && (
                    <div className="space-y-4 rounded-md border p-4">
                        <div className="text-sm">
                            <p className="font-medium">{match.full_name} · {match.national_id}</p>
                            <p>{match.company} ({match.business_registration_no})</p>
                            {match.is_owner && <p className="text-red-700">{t('This person is the Owner / Managing Director of the company.')}</p>}
                        </div>

                        <Form {...release.form()} className="space-y-3">
                            {({ processing, errors }) => (
                                <>
                                    <input type="hidden" name="national_id_no" value={nationalId} />
                                    <div className="grid gap-2">
                                        <Label htmlFor="reason">{t('Reason (required)')}</Label>
                                        <Textarea id="reason" name="reason" required minLength={10} maxLength={2000} />
                                        <InputError message={errors.reason ?? errors.national_id_no} />
                                    </div>
                                    <Button type="submit" variant="destructive" disabled={processing}>
                                        {t('Release national ID')}
                                    </Button>
                                </>
                            )}
                        </Form>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
