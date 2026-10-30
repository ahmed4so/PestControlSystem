import { Form, Head, usePage } from '@inertiajs/react';
import StatusMessage from '@/components/auth/status-message';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import { index, store as storeFms, update as updateFms } from '@/routes/admin/reference-data/fms';
import { store as storeRegion, update as updateRegion } from '@/routes/admin/reference-data/regions';

type Region = { id: number; federal_member_state_id: number; name_en: string; name_so: string; sort_order: number; is_active: boolean };
type State = { id: number; code: string; name_en: string; name_so: string; sort_order: number; is_active: boolean; regions: Region[] };

function ActiveSelect({ value }: { value: boolean }) {
    const { t } = useTranslation();

    return (
        <select name="is_active" defaultValue={value ? '1' : '0'} className="h-9 rounded-md border border-input bg-transparent px-2 text-sm">
            <option value="1">{t('Active')}</option>
            <option value="0">{t('Inactive')}</option>
        </select>
    );
}

/**
 * Federal Member States and regions (D-065). Values are deactivated, never
 * deleted. The seeded list is a draft until SARIS provides the official
 * one (D-184).
 */
export default function FmsPage({ states }: { states: State[] }) {
    const { t } = useTranslation();
    const { flash } = usePage<{ flash: { success?: string } }>().props;

    return (
        <AdminLayout breadcrumbs={[{ title: t('Reference data'), href: index().url }]}>
            <Head title={t('Reference data')} />

            <div className="flex flex-col gap-6 p-4">
                <div className="space-y-1">
                    <h1 className="text-2xl font-bold tracking-tight">{t('Federal Member States and regions')}</h1>
                    <p className="text-sm text-muted-foreground">{t('Deactivate a value to hide it from new forms. Values are never deleted.')}</p>
                </div>
                <StatusMessage status={flash?.success} />

                {states.map((state) => (
                    <section key={state.id} className="space-y-3 rounded-md border p-4">
                        <Form {...updateFms.form(state.id)} options={{ preserveScroll: true }} className="flex flex-wrap items-start gap-2">
                            {({ errors, processing }) => (
                                <>
                                    <Input name="code" defaultValue={state.code} className="w-32" aria-label={t('Code')} />
                                    <Input name="name_en" defaultValue={state.name_en} className="w-48" aria-label={t('Name in English')} />
                                    <Input name="name_so" defaultValue={state.name_so} className="w-48" aria-label={t('Name in Somali')} />
                                    <Input name="sort_order" type="number" defaultValue={state.sort_order} className="w-20" aria-label={t('Order')} />
                                    <ActiveSelect value={state.is_active} />
                                    <Button type="submit" size="sm" disabled={processing}>{t('Save')}</Button>
                                    <InputError message={errors.code ?? errors.name_en ?? errors.name_so} />
                                </>
                            )}
                        </Form>

                        <ul className="space-y-2 pl-4">
                            {state.regions.map((region) => (
                                <li key={region.id}>
                                    <Form {...updateRegion.form(region.id)} options={{ preserveScroll: true }} className="flex flex-wrap items-start gap-2">
                                        {({ processing }) => (
                                            <>
                                                <input type="hidden" name="federal_member_state_id" value={state.id} />
                                                <Input name="name_en" defaultValue={region.name_en} className="w-48" aria-label={t('Name in English')} />
                                                <Input name="name_so" defaultValue={region.name_so} className="w-48" aria-label={t('Name in Somali')} />
                                                <Input name="sort_order" type="number" defaultValue={region.sort_order} className="w-20" aria-label={t('Order')} />
                                                <ActiveSelect value={region.is_active} />
                                                <Button type="submit" size="sm" variant="secondary" disabled={processing}>{t('Save')}</Button>
                                            </>
                                        )}
                                    </Form>
                                </li>
                            ))}
                            <li>
                                <Form {...storeRegion.form()} options={{ preserveScroll: true }} resetOnSuccess className="flex flex-wrap items-start gap-2">
                                    {({ processing, errors }) => (
                                        <>
                                            <input type="hidden" name="federal_member_state_id" value={state.id} />
                                            <input type="hidden" name="sort_order" value={state.regions.length} />
                                            <input type="hidden" name="is_active" value="1" />
                                            <Input name="name_en" placeholder={t('New region (English)')} className="w-48" />
                                            <Input name="name_so" placeholder={t('New region (Somali)')} className="w-48" />
                                            <Button type="submit" size="sm" variant="outline" disabled={processing}>{t('Add region')}</Button>
                                            <InputError message={errors.name_en ?? errors.name_so} />
                                        </>
                                    )}
                                </Form>
                            </li>
                        </ul>
                    </section>
                ))}

                <section className="rounded-md border p-4">
                    <Form {...storeFms.form()} options={{ preserveScroll: true }} resetOnSuccess className="flex flex-wrap items-start gap-2">
                        {({ processing, errors }) => (
                            <>
                                <input type="hidden" name="sort_order" value={states.length} />
                                <input type="hidden" name="is_active" value="1" />
                                <Input name="code" placeholder={t('Code')} className="w-32" />
                                <Input name="name_en" placeholder={t('New state (English)')} className="w-48" />
                                <Input name="name_so" placeholder={t('New state (Somali)')} className="w-48" />
                                <Button type="submit" size="sm" disabled={processing}>{t('Add Federal Member State')}</Button>
                                <InputError message={errors.code ?? errors.name_en ?? errors.name_so} />
                            </>
                        )}
                    </Form>
                </section>
            </div>
        </AdminLayout>
    );
}
