import { Form, Head } from '@inertiajs/react';
import StatusMessage from '@/components/auth/status-message';
import AddressFields, { type AddressValues, type FmsOption } from '@/components/company/address-fields';
import NativeSelect from '@/components/company/native-select';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';
import { update } from '@/routes/portal/company';

type Props = {
    company: AddressValues & {
        id: number;
        name: string;
        business_registration_no: string;
        tax_compliance_no: string | null;
        email: string | null;
        phone: string | null;
        owner_person_id: number | null;
    };
    persons: { id: number; full_name: string }[];
    missing: string[];
    complete: boolean;
    amendmentRequired: boolean;
    canEdit: boolean;
    fms: FmsOption[];
};

/**
 * The company profile (05 §4.1). Saving keeps what is filled in; the list
 * shows what is still needed (D-178).
 */
export default function CompanyEdit({ company, persons, missing, complete, amendmentRequired, canEdit, fms }: Props) {
    const { t } = useTranslation();

    return (
        <PortalLayout>
            <Head title={t('Company')} />

            <div className="mx-auto max-w-3xl space-y-6">
                <h1 className="text-xl font-semibold">{company.name}</h1>

                {amendmentRequired && (
                    <StatusMessage tone="error" status={t('SARIS removed a person from your profile. Please update your profile and submit an amendment of your license.')} />
                )}
                {!canEdit && (
                    <StatusMessage tone="error" status={t('Your company data is locked while an application is being processed.')} />
                )}

                {complete ? (
                    <StatusMessage status={t('Your company profile is complete.')} />
                ) : (
                    <div className="rounded-md border p-4 text-sm">
                        <p className="mb-2 font-medium">{t('Still needed for a complete profile:')}</p>
                        <ul className="list-disc space-y-1 pl-5">
                            {missing.map((item) => (
                                <li key={item}>{t(item)}</li>
                            ))}
                        </ul>
                    </div>
                )}

                <Form {...update.form()} options={{ preserveScroll: true }} className="space-y-6">
                    {({ processing, errors }) => (
                        <fieldset disabled={!canEdit} className="space-y-6">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="name">{t('Company name')}</Label>
                                    <Input id="name" name="name" defaultValue={company.name} required maxLength={200} />
                                    <InputError message={errors.name} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="business_registration_no">{t('Business registration number')}</Label>
                                    <Input id="business_registration_no" name="business_registration_no" defaultValue={company.business_registration_no} required maxLength={60} />
                                    <InputError message={errors.business_registration_no} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="tax_compliance_no">{t('Tax compliance certificate number')}</Label>
                                    <Input id="tax_compliance_no" name="tax_compliance_no" defaultValue={company.tax_compliance_no ?? ''} maxLength={60} />
                                    <InputError message={errors.tax_compliance_no} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="email">{t('Company email')}</Label>
                                    <Input id="email" name="email" type="email" defaultValue={company.email ?? ''} maxLength={190} />
                                    <InputError message={errors.email} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="phone">{t('Company phone')}</Label>
                                    <Input id="phone" name="phone" type="tel" defaultValue={company.phone ?? ''} placeholder="+252 61 234 5678" />
                                    <InputError message={errors.phone} />
                                </div>
                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="owner_person_id">{t('Owner / Managing Director')}</Label>
                                    <NativeSelect id="owner_person_id" name="owner_person_id" defaultValue={company.owner_person_id ?? ''}>
                                        <option value="">{persons.length === 0 ? t('Add the person first under Persons') : t('Choose…')}</option>
                                        {persons.map((person) => (
                                            <option key={person.id} value={person.id}>
                                                {person.full_name}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                    <InputError message={errors.owner_person_id} />
                                </div>
                            </div>

                            <AddressFields fms={fms} values={company} errors={errors} />

                            {canEdit && (
                                <Button type="submit" disabled={processing}>
                                    {processing && <Spinner />}
                                    {t('Save')}
                                </Button>
                            )}
                        </fieldset>
                    )}
                </Form>
            </div>
        </PortalLayout>
    );
}
