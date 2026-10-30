import { Form, Head } from '@inertiajs/react';
import StatusMessage from '@/components/auth/status-message';
import AddressFields, { type AddressValues, type FmsOption } from '@/components/company/address-fields';
import DocumentUpload from '@/components/company/document-upload';
import NativeSelect from '@/components/company/native-select';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';
import { store, update } from '@/routes/portal/persons';

type Person = AddressValues & {
    id: number;
    full_name: string;
    national_id_no: string;
    nationality: string | null;
    email: string | null;
    phone: string | null;
    qualification: string | null;
    institution: string | null;
    years_of_experience: number | null;
    training_pcp_handling: string | null;
    training_fumigation: string | null;
    released: boolean;
    documents: { id: number; type: string; label: string; uploaded_at: string | null }[];
};

type Props = {
    person: Person | null;
    canEdit: boolean;
    personDocumentTypes: { value: string; label: string }[];
    fms: FmsOption[];
    countries: { code: string; name: string }[];
};

/**
 * Add or edit a person (05 §4.2). The national ID can be registered in one
 * company only (D-066).
 */
export default function PersonForm({ person, canEdit, personDocumentTypes, fms, countries }: Props) {
    const { t } = useTranslation();
    const form = person ? update.form(person.id) : store.form();

    return (
        <PortalLayout>
            <Head title={person ? person.full_name : t('Add person')} />

            <div className="mx-auto max-w-3xl space-y-6">
                <h1 className="text-xl font-semibold">{person ? person.full_name : t('Add person')}</h1>

                {person?.released && (
                    <StatusMessage tone="error" status={t('SARIS released this person to another company. The record is kept for your history and cannot be changed.')} />
                )}

                <Form {...form} options={{ preserveScroll: true }} className="space-y-6">
                    {({ processing, errors }) => (
                        <fieldset disabled={!canEdit} className="space-y-6">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="full_name">{t('Full name')}</Label>
                                    <Input id="full_name" name="full_name" defaultValue={person?.full_name ?? ''} required maxLength={150} />
                                    <InputError message={errors.full_name} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="national_id_no">{t('National ID number (11 digits)')}</Label>
                                    <Input id="national_id_no" name="national_id_no" inputMode="numeric" defaultValue={person?.national_id_no ?? ''} required maxLength={11} autoComplete="off" />
                                    <InputError message={errors.national_id_no} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="nationality">{t('Nationality')}</Label>
                                    <NativeSelect id="nationality" name="nationality" defaultValue={person?.nationality ?? 'SO'}>
                                        {countries.map((country) => (
                                            <option key={country.code} value={country.code}>
                                                {country.name}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                    <InputError message={errors.nationality} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="email">{t('Email address')}</Label>
                                    <Input id="email" name="email" type="email" defaultValue={person?.email ?? ''} maxLength={190} />
                                    <InputError message={errors.email} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="phone">{t('Phone')}</Label>
                                    <Input id="phone" name="phone" type="tel" defaultValue={person?.phone ?? ''} placeholder="+252 61 234 5678" />
                                    <InputError message={errors.phone} />
                                </div>
                            </div>

                            <AddressFields fms={fms} values={person} errors={errors} />

                            <fieldset className="grid gap-4 sm:grid-cols-2">
                                <legend className="mb-2 text-sm font-medium">{t('Technical person (fill in if this person is your technical person)')}</legend>
                                <div className="grid gap-2">
                                    <Label htmlFor="qualification">{t('Qualification')}</Label>
                                    <Input id="qualification" name="qualification" defaultValue={person?.qualification ?? ''} placeholder={t('e.g. BSc Agriculture')} maxLength={255} />
                                    <InputError message={errors.qualification} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="institution">{t('University or institution')}</Label>
                                    <Input id="institution" name="institution" defaultValue={person?.institution ?? ''} maxLength={255} />
                                    <InputError message={errors.institution} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="years_of_experience">{t('Years of experience')}</Label>
                                    <Input id="years_of_experience" name="years_of_experience" type="number" min={0} max={60} defaultValue={person?.years_of_experience ?? ''} />
                                    <InputError message={errors.years_of_experience} />
                                </div>
                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="training_pcp_handling">{t('Training in pest control product handling')}</Label>
                                    <Textarea id="training_pcp_handling" name="training_pcp_handling" defaultValue={person?.training_pcp_handling ?? ''} maxLength={2000} />
                                    <InputError message={errors.training_pcp_handling} />
                                </div>
                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="training_fumigation">{t('Training in fumigation (Operator License)')}</Label>
                                    <Textarea id="training_fumigation" name="training_fumigation" defaultValue={person?.training_fumigation ?? ''} maxLength={2000} />
                                    <InputError message={errors.training_fumigation} />
                                </div>
                            </fieldset>

                            {canEdit && (
                                <Button type="submit" disabled={processing}>
                                    {processing && <Spinner />}
                                    {t('Save')}
                                </Button>
                            )}
                        </fieldset>
                    )}
                </Form>

                {person && !person.released && canEdit && (
                    <section className="space-y-4">
                        <h2 className="text-lg font-medium">{t('Degree certificate and CV')}</h2>
                        {personDocumentTypes.map((type) => {
                            const current = person.documents.find((document) => document.type === type.value);

                            return (
                                <div key={type.value} className="space-y-2 rounded-md border p-4">
                                    <p className="font-medium">{t(type.label)}</p>
                                    {current && <p className="text-sm text-muted-foreground">{t('Uploaded. A new upload replaces it.')}</p>}
                                    <DocumentUpload type={type.value} personId={person.id} />
                                </div>
                            );
                        })}
                    </section>
                )}
            </div>
        </PortalLayout>
    );
}
