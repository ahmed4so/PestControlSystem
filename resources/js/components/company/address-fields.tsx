import { useState } from 'react';
import NativeSelect from '@/components/company/native-select';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/lib/i18n';

export type FmsOption = {
    id: number;
    name: string;
    regions: { id: number; name: string }[];
};

export type AddressValues = {
    address_fms_id?: number | null;
    address_region_id?: number | null;
    address_district?: string | null;
    address_town_or_market?: string | null;
    address_street?: string | null;
    address_details?: string | null;
};

/**
 * The structured address (05 §4.5, D-065): the region list follows the
 * chosen Federal Member State.
 */
export default function AddressFields({
    fms,
    values,
    errors,
    disabled = false,
}: {
    fms: FmsOption[];
    values?: AddressValues | null;
    errors: Record<string, string | undefined>;
    disabled?: boolean;
}) {
    const { t } = useTranslation();
    const [fmsId, setFmsId] = useState<string>(values?.address_fms_id ? String(values.address_fms_id) : '');
    const regions = fms.find((option) => String(option.id) === fmsId)?.regions ?? [];

    return (
        <fieldset className="grid gap-4 sm:grid-cols-2" disabled={disabled}>
            <legend className="mb-2 text-sm font-medium">{t('Address')}</legend>

            <div className="grid gap-2">
                <Label htmlFor="address_fms_id">{t('Federal Member State')}</Label>
                <NativeSelect
                    id="address_fms_id"
                    name="address_fms_id"
                    value={fmsId}
                    onChange={(event) => setFmsId(event.target.value)}
                >
                    <option value="">{t('Choose…')}</option>
                    {fms.map((option) => (
                        <option key={option.id} value={option.id}>
                            {option.name}
                        </option>
                    ))}
                </NativeSelect>
                <InputError message={errors.address_fms_id} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="address_region_id">{t('Region')}</Label>
                <NativeSelect
                    key={fmsId}
                    id="address_region_id"
                    name="address_region_id"
                    defaultValue={values?.address_region_id ?? ''}
                    disabled={regions.length === 0}
                >
                    <option value="">{t('Choose…')}</option>
                    {regions.map((region) => (
                        <option key={region.id} value={region.id}>
                            {region.name}
                        </option>
                    ))}
                </NativeSelect>
                <InputError message={errors.address_region_id} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="address_town_or_market">{t('Town or market')}</Label>
                <Input id="address_town_or_market" name="address_town_or_market" defaultValue={values?.address_town_or_market ?? ''} maxLength={150} />
                <InputError message={errors.address_town_or_market} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="address_district">{t('District (optional)')}</Label>
                <Input id="address_district" name="address_district" defaultValue={values?.address_district ?? ''} maxLength={100} />
                <InputError message={errors.address_district} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="address_street">{t('Street (optional)')}</Label>
                <Input id="address_street" name="address_street" defaultValue={values?.address_street ?? ''} maxLength={150} />
                <InputError message={errors.address_street} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="address_details">{t('Building or landmark (optional)')}</Label>
                <Input id="address_details" name="address_details" defaultValue={values?.address_details ?? ''} maxLength={255} />
                <InputError message={errors.address_details} />
            </div>
        </fieldset>
    );
}
