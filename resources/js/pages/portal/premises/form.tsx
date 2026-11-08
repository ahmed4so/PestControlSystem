import { Form, Head } from '@inertiajs/react';
import BackLink from '@/components/back-link';
import AddressFields, { type AddressValues, type FmsOption } from '@/components/company/address-fields';
import NativeSelect from '@/components/company/native-select';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';
import { index as backTo, store, update } from '@/routes/portal/premises';

type Premises = AddressValues & {
    id: number;
    label: string;
    premises_type: string;
    gps_lat: string | null;
    gps_lng: string | null;
    waste_disposal_method: string | null;
};

type Props = {
    premises: Premises | null;
    canEdit: boolean;
    types: { value: string; label: string }[];
    fms: FmsOption[];
};

/**
 * A premises (05 §4.3). The facility checklist is checked by the Inspector
 * during the inspection, not filled in by the company (D-192).
 */
export default function PremisesForm({ premises, canEdit, types, fms }: Props) {
    const { t } = useTranslation();
    const form = premises ? update.form(premises.id) : store.form();

    return (
        <PortalLayout>
            <Head title={premises ? premises.label : t('Add premises')} />

            <div className="mx-auto max-w-3xl space-y-8">
                <BackLink href={backTo()}>{t('Back to premises')}</BackLink>
                <h1 className="text-xl font-semibold">{premises ? premises.label : t('Add premises')}</h1>

                <Form {...form} options={{ preserveScroll: true }} className="space-y-6">
                    {({ processing, errors }) => (
                        <fieldset disabled={!canEdit} className="space-y-6">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="label">{t('Name of the premises')}</Label>
                                    <Input id="label" name="label" defaultValue={premises?.label ?? ''} placeholder={t('e.g. Main warehouse, Hodan')} required maxLength={100} />
                                    <InputError message={errors.label} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="premises_type">{t('Type')}</Label>
                                    <NativeSelect id="premises_type" name="premises_type" defaultValue={premises?.premises_type ?? ''} required>
                                        <option value="">{t('Choose…')}</option>
                                        {types.map((type) => (
                                            <option key={type.value} value={type.value}>
                                                {t(type.label)}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                    <InputError message={errors.premises_type} />
                                </div>
                            </div>

                            <AddressFields fms={fms} values={premises} errors={errors} />

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="gps_lat">{t('GPS latitude (optional)')}</Label>
                                    <Input id="gps_lat" name="gps_lat" inputMode="decimal" defaultValue={premises?.gps_lat ?? ''} />
                                    <InputError message={errors.gps_lat} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="gps_lng">{t('GPS longitude (optional)')}</Label>
                                    <Input id="gps_lng" name="gps_lng" inputMode="decimal" defaultValue={premises?.gps_lng ?? ''} />
                                    <InputError message={errors.gps_lng} />
                                </div>
                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="waste_disposal_method">{t('Waste disposal method (needed for Import Permit storage)')}</Label>
                                    <Input id="waste_disposal_method" name="waste_disposal_method" defaultValue={premises?.waste_disposal_method ?? ''} maxLength={255} />
                                    <InputError message={errors.waste_disposal_method} />
                                </div>
                            </div>

                            {canEdit && (
                                <Button type="submit" disabled={processing}>
                                    {processing && <Spinner />}
                                    {t('Save')}
                                </Button>
                            )}
                        </fieldset>
                    )}
                </Form>

                <p className="text-sm text-muted-foreground">
                    {t('SARIS checks the facilities and safety equipment of your premises during the inspection.')}
                </p>
            </div>
        </PortalLayout>
    );
}
