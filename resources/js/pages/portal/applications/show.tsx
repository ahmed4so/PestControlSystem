import { Form, Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import BackLink from '@/components/back-link';
import NativeSelect from '@/components/company/native-select';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import PortalLayout from '@/layouts/portal-layout';
import { useTranslation } from '@/lib/i18n';
import { index as backTo, premises as premisesRoute, submit as submitRoute, technicalPerson } from '@/routes/portal/applications';
import { edit as companyEdit } from '@/routes/portal/company';
import { index as documentsIndex } from '@/routes/portal/documents';
import { edit as personEdit } from '@/routes/portal/persons';

type Props = {
    application: {
        id: number;
        reference: string | null;
        service: string;
        category: string | null;
        type: string;
        status: string;
        status_label: string;
        current_version_no: number;
    };
    editable: boolean;
    premises: { id: number; label: string; town: string | null }[];
    persons: { id: number; name: string; qualification: string | null }[];
    selected: { premises: number[]; person_id: number | null };
    submit: { action: string; blocked_by: string[] } | null;
    declarationName: string;
    returned: { comment: string | null; items: { label: string; result: string; comment: string | null }[] } | null;
    inspections: { premises: string; planned_date: string }[];
    versions: { version_no: number; submitted_at: string }[];
    history: { date: string; status: string; comment: string | null }[];
};

const date = (value: string) => new Date(value).toLocaleDateString();

/**
 * A Company License application (Form 1): choose premises and the
 * technical person, sign the declaration, submit; on return, fix and
 * resubmit (T1, T25). The company sees only what D-201 allows.
 */
export default function ApplicationShow({ application, editable, premises, persons, selected, submit, declarationName, returned, inspections, versions, history }: Props) {
    const { t } = useTranslation();
    const [chosen, setChosen] = useState<number[]>(selected.premises);
    const [saving, setSaving] = useState(false);
    const resubmit = application.status === 'RETURNED';

    const savePremises = (ids: number[]) => {
        setChosen(ids);
        setSaving(true);
        router.put(premisesRoute.url(application.id), { premises: ids }, { preserveScroll: true, onFinish: () => setSaving(false) });
    };

    const savePerson = (value: string) => {
        router.put(technicalPerson.url(application.id), { person_id: value === '' ? null : Number(value) }, { preserveScroll: true });
    };

    return (
        <PortalLayout>
            <Head title={application.reference ?? t('Draft')} />

            <div className="mx-auto max-w-3xl space-y-8">
                <BackLink href={backTo()}>{t('Back to applications')}</BackLink>
                <div className="space-y-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <h1 className="text-xl font-semibold">{application.reference ?? t('Draft')}</h1>
                        <Badge variant="secondary">{t(application.status_label)}</Badge>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        {t(application.service)}
                        {application.category && ` — ${t(application.category)}`}
                    </p>
                </div>

                {returned && (
                    <Alert>
                        <AlertTitle>{t('SARIS returned your application for correction')}</AlertTitle>
                        <AlertDescription className="space-y-2">
                            {returned.items.length > 0 && (
                                <ul className="list-disc space-y-1 pl-5">
                                    {returned.items.map((item) => (
                                        <li key={item.label}>
                                            <span className="font-medium">{t(item.label)}</span> ({t(item.result)}): {item.comment}
                                        </li>
                                    ))}
                                </ul>
                            )}
                            {returned.comment && <p>{returned.comment}</p>}
                        </AlertDescription>
                    </Alert>
                )}

                {inspections.length > 0 && (
                    <Alert>
                        <AlertTitle>{t('Inspection planned')}</AlertTitle>
                        <AlertDescription>
                            <ul className="space-y-1">
                                {inspections.map((inspection) => (
                                    <li key={inspection.premises}>
                                        {inspection.premises}: {date(inspection.planned_date)}
                                    </li>
                                ))}
                            </ul>
                        </AlertDescription>
                    </Alert>
                )}

                {editable && (
                    <section className="space-y-3">
                        <h2 className="font-semibold">{t('Premises')}</h2>
                        {premises.length === 0 ? (
                            <p className="text-sm text-muted-foreground">{t('Add your premises in the company profile first.')}</p>
                        ) : (
                            <ul className="space-y-2">
                                {premises.map((item) => (
                                    <li key={item.id} className="flex items-center gap-2">
                                        <Checkbox
                                            id={`premises-${item.id}`}
                                            checked={chosen.includes(item.id)}
                                            disabled={saving}
                                            onCheckedChange={(checked) =>
                                                savePremises(checked ? [...chosen, item.id] : chosen.filter((id) => id !== item.id))
                                            }
                                        />
                                        <Label htmlFor={`premises-${item.id}`}>
                                            {item.label}
                                            {item.town && <span className="text-muted-foreground"> — {item.town}</span>}
                                        </Label>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                )}

                {editable && (
                    <section className="space-y-3">
                        <h2 className="font-semibold">{t('Technical person')}</h2>
                        <NativeSelect id="person_id" defaultValue={selected.person_id ?? ''} onChange={(event) => savePerson(event.target.value)}>
                            <option value="">{t('Choose…')}</option>
                            {persons.map((person) => (
                                <option key={person.id} value={person.id}>
                                    {person.name}
                                    {person.qualification && ` (${person.qualification})`}
                                </option>
                            ))}
                        </NativeSelect>
                    </section>
                )}

                {submit && (
                    <section className="space-y-4 rounded-md border p-4">
                        <h2 className="font-semibold">{resubmit ? t('Resubmit the application') : t('Submit the application')}</h2>

                        {submit.blocked_by.length > 0 && (
                            <div role="alert" className="space-y-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200">
                                <p className="font-medium">{t('Before you can submit:')}</p>
                                <ul className="list-disc space-y-1 pl-5">
                                    {submit.blocked_by.map((reason) => (
                                        <li key={reason}>{reason}</li>
                                    ))}
                                </ul>
                                <p className="flex flex-wrap gap-x-3 gap-y-1 pt-1">
                                    <span>{t('Fix it here:')}</span>
                                    {selected.person_id && (
                                        <Link href={personEdit(selected.person_id)} className="font-medium underline">
                                            {t('Technical person (degree certificate, CV)')}
                                        </Link>
                                    )}
                                    <Link href={documentsIndex()} className="font-medium underline">
                                        {t('Company documents')}
                                    </Link>
                                    <Link href={companyEdit()} className="font-medium underline">
                                        {t('Company profile')}
                                    </Link>
                                </p>
                            </div>
                        )}

                        <Form {...submitRoute.form(application.id)} options={{ preserveScroll: true }} resetOnError={['password']} className="space-y-4">
                            {({ processing, errors }) => (
                                <>
                                    <input type="hidden" name="expected_status" value={application.status} />
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="declaration_name">{t('Your name')}</Label>
                                            <Input id="declaration_name" name="declaration_name" defaultValue={declarationName} required maxLength={150} />
                                            <InputError message={errors.declaration_name} />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="declaration_designation">{t('Your position in the company')}</Label>
                                            <Input id="declaration_designation" name="declaration_designation" required maxLength={150} />
                                            <InputError message={errors.declaration_designation} />
                                        </div>
                                    </div>

                                    {resubmit && (
                                        <div className="grid gap-2">
                                            <Label htmlFor="response_note">{t('Your response to SARIS (optional)')}</Label>
                                            <Textarea id="response_note" name="response_note" maxLength={5000} />
                                            <InputError message={errors.response_note} />
                                        </div>
                                    )}

                                    <div className="flex items-start gap-2">
                                        <Checkbox id="confirm" name="confirm" value="1" />
                                        <Label htmlFor="confirm">{t('I confirm that the information in this application is true.')}</Label>
                                    </div>
                                    <InputError message={errors.confirm} />

                                    <div className="grid gap-2 sm:max-w-xs">
                                        <Label htmlFor="password">{t('Your password')}</Label>
                                        <Input id="password" name="password" type="password" autoComplete="current-password" required />
                                        <InputError message={errors.password} />
                                    </div>

                                    <InputError message={errors.submit ?? errors.expected_status} />

                                    <Button type="submit" disabled={processing || submit.blocked_by.length > 0}>
                                        {processing && <Spinner />}
                                        {resubmit ? t('Resubmit') : t('Submit')}
                                    </Button>
                                </>
                            )}
                        </Form>
                    </section>
                )}

                {versions.length > 0 && (
                    <section className="space-y-2">
                        <h2 className="font-semibold">{t('Submitted versions')}</h2>
                        <ul className="text-sm">
                            {versions.map((version) => (
                                <li key={version.version_no}>
                                    {t('Version :number', { number: String(version.version_no) })} — {date(version.submitted_at)}
                                </li>
                            ))}
                        </ul>
                    </section>
                )}

                {history.length > 0 && (
                    <section className="space-y-2">
                        <h2 className="font-semibold">{t('History')}</h2>
                        <ol className="space-y-2 text-sm">
                            {history.map((entry, index) => (
                                <li key={index} className="border-l-2 pl-3">
                                    <span className="text-muted-foreground">{date(entry.date)}</span> — {t(entry.status)}
                                    {entry.comment && <p className="mt-1">{entry.comment}</p>}
                                </li>
                            ))}
                        </ol>
                    </section>
                )}
            </div>
        </PortalLayout>
    );
}
