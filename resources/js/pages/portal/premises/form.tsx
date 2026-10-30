import { Form, Head, router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import AddressFields, { type AddressValues, type FmsOption } from '@/components/company/address-fields';
import NativeSelect from '@/components/company/native-select';
import PhotoUpload from '@/components/company/photo-upload';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';
import { store, update } from '@/routes/portal/premises';
import { update as updateItem } from '@/routes/portal/premises/checklist';
import { destroy as destroyPhoto, store as storePhoto } from '@/routes/portal/premises/photos';

type ChecklistRow = {
    id: number;
    code: string;
    label: string;
    is_available: boolean;
    notes: string | null;
    photos: { id: number; url: string }[];
};

type Premises = AddressValues & {
    id: number;
    label: string;
    premises_type: string;
    gps_lat: string | null;
    gps_lng: string | null;
    waste_disposal_method: string | null;
    checklist: ChecklistRow[];
};

type Props = {
    premises: Premises | null;
    canEdit: boolean;
    types: { value: string; label: string }[];
    maxPhotos: number;
    fms: FmsOption[];
};

/**
 * A premises and its facility checklist (05 §4.3): each item has
 * "available", notes and 1 to 5 photos (D-018, D-135).
 */
export default function PremisesForm({ premises, canEdit, types, maxPhotos, fms }: Props) {
    const { t } = useTranslation();
    const form = premises ? update.form(premises.id) : store.form();

    return (
        <PortalLayout>
            <Head title={premises ? premises.label : t('Add premises')} />

            <div className="mx-auto max-w-3xl space-y-8">
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

                {premises && (
                    <section className="space-y-4">
                        <div>
                            <h2 className="text-lg font-medium">{t('Facility and safety checklist')}</h2>
                            <p className="text-sm text-muted-foreground">
                                {t('Each item you have needs at least one photo, up to :max.', { max: maxPhotos })}
                            </p>
                        </div>

                        {premises.checklist.map((item) => (
                            <div key={item.id} className="space-y-3 rounded-md border p-4">
                                <Form
                                    {...updateItem.form({ premises: premises.id, item: item.id })}
                                    options={{ preserveScroll: true }}
                                    className="space-y-3"
                                >
                                    {({ processing, errors }) => (
                                        <fieldset disabled={!canEdit} className="space-y-3">
                                            <div className="flex flex-wrap items-center gap-3">
                                                <p className="font-medium">{t(item.label)}</p>
                                                <NativeSelect name="is_available" defaultValue={item.is_available ? '1' : '0'} className="w-auto" aria-label={t('Available')}>
                                                    <option value="1">{t('Available')}</option>
                                                    <option value="0">{t('Not available')}</option>
                                                </NativeSelect>
                                            </div>
                                            <Textarea name="notes" defaultValue={item.notes ?? ''} placeholder={t('Notes (optional)')} maxLength={2000} rows={2} />
                                            <InputError message={errors.is_available ?? errors.notes} />
                                            {canEdit && (
                                                <Button type="submit" size="sm" variant="secondary" disabled={processing}>
                                                    {t('Save')}
                                                </Button>
                                            )}
                                        </fieldset>
                                    )}
                                </Form>

                                <div className="flex flex-wrap gap-2">
                                    {item.photos.map((photo) => (
                                        <div key={photo.id} className="flex items-center gap-1 rounded border px-2 py-1 text-sm">
                                            <a href={photo.url} className="underline">
                                                {t('Photo')}
                                            </a>
                                            {canEdit && (
                                                <button
                                                    type="button"
                                                    aria-label={t('Remove photo')}
                                                    onClick={() =>
                                                        router.delete(destroyPhoto.url({ premises: premises.id, photo: photo.id }), { preserveScroll: true })
                                                    }
                                                >
                                                    <Trash2 className="size-4" />
                                                </button>
                                            )}
                                        </div>
                                    ))}
                                </div>

                                {canEdit && item.photos.length < maxPhotos && (
                                    <PhotoUpload url={storePhoto.url({ premises: premises.id, item: item.id })} />
                                )}
                            </div>
                        ))}
                    </section>
                )}
            </div>
        </PortalLayout>
    );
}
