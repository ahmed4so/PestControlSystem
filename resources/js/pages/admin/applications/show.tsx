import { Form, Head, Link, usePage } from '@inertiajs/react';
import NativeSelect from '@/components/company/native-select';
import InputError from '@/components/input-error';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin-layout';
import { useTranslation } from '@/lib/i18n';
import { queues } from '@/routes/admin';
import { actions as actionsRoute, reviewItems as reviewItemsRoute, show as applicationShow } from '@/routes/admin/applications';
import { show as companyShow } from '@/routes/admin/companies';
import { show as inspectionShow } from '@/routes/admin/inspections';

type Action = { action: string; label: string; to: string | null; requires_comment: boolean; requires_password: boolean; blocked_by: string[] };
type Recorded = { stage: string; code: string; label: string; result: string; comment: string | null; mine: boolean; reviewer: string | null; reviewed_at: string };
type Change = { field: string; old: unknown; new: unknown };

type Props = {
    application: {
        id: number;
        reference: string | null;
        service: string;
        category: string | null;
        status: string;
        stage: string;
        company: { id: number; name: string | null };
    };
    snapshot: Record<string, unknown> | null;
    versions: { version_no: number; submitted_at: string; declaration: string; response_note: string | null; changes: Change[] }[];
    reviewItems: { stage: string | null; items: { code: string; label: string }[]; recorded: Recorded[] };
    assignments: { stage: string; user: string | null; assigned_at: string; ended_at: string | null }[];
    timeline: { id: number; action: string; from: string; to: string; actor: string | null; role: string | null; on_behalf_of: string | null; comment: string | null; at: string }[];
    actions: Action[];
    technicalReviewers: { id: number; name: string }[];
    inspections: { id: number; round: number; premises: string | null; inspector: string | null; planned_date: string; conducted_at: string | null; signed_at: string | null; status: string }[];
    schedule: { premises: { id: number; label: string }[]; inspectors: { id: number; name: string }[] } | null;
};

/** Actions on one inspection are taken from the inspection page. */
const INSPECTION_ACTIONS = ['RECORD_INSPECTION_VISIT', 'SUBMIT_INSPECTION_REPORT', 'RESCHEDULE_INSPECTION'];

const RESULTS = ['COMPLIANT', 'DEFICIENT', 'CLARIFICATION_REQUIRED'] as const;
const RESULT_LABELS: Record<string, string> = { COMPLIANT: 'Compliant', DEFICIENT: 'Deficient', CLARIFICATION_REQUIRED: 'Clarification required' };
const when = (value: string) => new Date(value).toLocaleString();
const show = (value: unknown) => (value === null || value === undefined || value === '' ? '—' : String(value));

/**
 * One section of the snapshot, as nested definition lists.
 */
function SnapshotSection({ value }: { value: unknown }) {
    if (Array.isArray(value)) {
        return (
            <div className="space-y-2">
                {value.map((entry, index) => (
                    <div key={index} className="rounded border p-2">
                        <SnapshotSection value={entry} />
                    </div>
                ))}
            </div>
        );
    }

    if (value !== null && typeof value === 'object') {
        return (
            <dl className="grid gap-x-4 gap-y-1 text-sm sm:grid-cols-[12rem_1fr]">
                {Object.entries(value as Record<string, unknown>).map(([key, inner]) => (
                    <div key={key} className="contents">
                        <dt className="text-muted-foreground">{key.replaceAll('_', ' ')}</dt>
                        <dd className="break-words">{inner !== null && typeof inner === 'object' ? <SnapshotSection value={inner} /> : show(inner)}</dd>
                    </div>
                ))}
            </dl>
        );
    }

    return <span>{show(value)}</span>;
}

/**
 * The application file (04 §15, D-042): the submitted snapshot reviewers
 * work from, the versions and their differences (D-199), review items, the
 * timeline and the actions the user may take (D-191).
 */
export default function ApplicationFile({ application, snapshot, versions, reviewItems, assignments, timeline, actions, technicalReviewers, inspections, schedule }: Props) {
    const shownActions = actions.filter((action) => !INSPECTION_ACTIONS.includes(action.action));
    const { t } = useTranslation();
    const { auth } = usePage().props;
    const seesCompanies = auth.permissions?.includes('view applications') ?? false;
    const currentResult = (code: string) => reviewItems.recorded.find((item) => item.stage === reviewItems.stage && item.code === code);

    return (
        <AdminLayout
            breadcrumbs={[
                { title: t('Work queues'), href: queues().url },
                { title: application.reference ?? t('Draft'), href: applicationShow(application.id).url },
            ]}
        >
            <Head title={application.reference ?? t('Draft')} />

            <div className="space-y-8 p-4">
                <div className="space-y-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <h1 className="text-xl font-semibold">{application.reference ?? t('Draft')}</h1>
                        <Badge>{application.stage}</Badge>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        {seesCompanies ? (
                            <Link href={companyShow(application.company.id)} className="underline">
                                {application.company.name}
                            </Link>
                        ) : (
                            application.company.name
                        )}{' '}
                        — {t(application.service)}
                        {application.category && ` — ${t(application.category)}`}
                    </p>
                </div>

                {shownActions.length > 0 && (
                    <section className="space-y-3">
                        <h2 className="font-semibold">{t('Actions')}</h2>
                        <div className="grid gap-3 md:grid-cols-2">
                            {shownActions.map((action) => (
                                <Form key={action.action} {...actionsRoute.form(application.id)} options={{ preserveScroll: true }} resetOnSuccess className="space-y-2 rounded-md border p-3">
                                    {({ processing, errors }) => (
                                        <>
                                            <input type="hidden" name="action" value={action.action} />
                                            <input type="hidden" name="expected_status" value={application.status} />
                                            <p className="font-medium">{t(action.label)}</p>

                                            {action.action === 'ASSIGN_TECHNICAL_REVIEWER' && (
                                                <NativeSelect name="assignee_id" defaultValue="" required>
                                                    <option value="">{t('Choose a Technical Reviewer…')}</option>
                                                    {technicalReviewers.map((reviewer) => (
                                                        <option key={reviewer.id} value={reviewer.id}>
                                                            {reviewer.name}
                                                        </option>
                                                    ))}
                                                </NativeSelect>
                                            )}

                                            {action.action === 'SCHEDULE_INSPECTION' && schedule && (
                                                <div className="space-y-2">
                                                    {schedule.premises.map((premises, index) => (
                                                        <div key={premises.id} className="grid gap-2 rounded border p-2">
                                                            <input type="hidden" name={`inspections[${index}][premises_id]`} value={premises.id} />
                                                            <span className="text-sm font-medium">{premises.label}</span>
                                                            <NativeSelect name={`inspections[${index}][inspector_id]`} defaultValue="" required>
                                                                <option value="">{t('Choose an Inspector…')}</option>
                                                                {schedule.inspectors.map((inspector) => (
                                                                    <option key={inspector.id} value={inspector.id}>
                                                                        {inspector.name}
                                                                    </option>
                                                                ))}
                                                            </NativeSelect>
                                                            <Input type="date" name={`inspections[${index}][planned_date]`} required />
                                                        </div>
                                                    ))}
                                                </div>
                                            )}

                                            {action.requires_comment && <Textarea name="comment" required maxLength={2000} placeholder={t('Comment')} />}
                                            {action.requires_password && <Input name="password" type="password" autoComplete="current-password" required placeholder={t('Your password')} />}

                                            {action.blocked_by.length > 0 && (
                                                <ul className="list-disc pl-5 text-sm text-muted-foreground">
                                                    {action.blocked_by.map((reason) => (
                                                        <li key={reason}>{reason}</li>
                                                    ))}
                                                </ul>
                                            )}

                                            <InputError message={errors.action ?? errors.comment ?? errors.password} />
                                            <Button type="submit" size="sm" disabled={processing || action.blocked_by.length > 0}>
                                                {processing && <Spinner />}
                                                {t('Confirm')}
                                            </Button>
                                        </>
                                    )}
                                </Form>
                            ))}
                        </div>
                    </section>
                )}

                {reviewItems.stage && reviewItems.items.length > 0 && (
                    <section className="space-y-3">
                        <h2 className="font-semibold">{t('Review items')}</h2>
                        <Form {...reviewItemsRoute.form(application.id)} options={{ preserveScroll: true }} className="space-y-3">
                            {({ processing, errors }) => (
                                <>
                                    <input type="hidden" name="stage" value={reviewItems.stage ?? ''} />
                                    {reviewItems.items.map((item) => {
                                        const recorded = currentResult(item.code);

                                        return (
                                            <div key={item.code} className="grid gap-2 rounded-md border p-3 sm:grid-cols-[1fr_14rem]">
                                                <div className="space-y-1">
                                                    <Label htmlFor={`result-${item.code}`}>{t(item.label)}</Label>
                                                    {recorded && !recorded.mine && (
                                                        <p className="text-xs text-muted-foreground">
                                                            {t('Recorded by :name', { name: recorded.reviewer ?? '' })}
                                                        </p>
                                                    )}
                                                    <Textarea name={`items[${item.code}][comment]`} defaultValue={recorded?.comment ?? ''} maxLength={2000} placeholder={t('Comment (required unless compliant)')} />
                                                </div>
                                                <NativeSelect id={`result-${item.code}`} name={`items[${item.code}][result]`} defaultValue={recorded?.result ?? ''} required>
                                                    <option value="">{t('Choose…')}</option>
                                                    {RESULTS.map((result) => (
                                                        <option key={result} value={result}>
                                                            {t(RESULT_LABELS[result])}
                                                        </option>
                                                    ))}
                                                </NativeSelect>
                                            </div>
                                        );
                                    })}
                                    <InputError message={errors.items} />
                                    <Button type="submit" disabled={processing}>
                                        {processing && <Spinner />}
                                        {t('Save review items')}
                                    </Button>
                                </>
                            )}
                        </Form>
                    </section>
                )}

                {reviewItems.recorded.length > 0 && (
                    <section className="space-y-2">
                        <h2 className="font-semibold">{t('Recorded results (current version)')}</h2>
                        <ul className="divide-y rounded-md border text-sm">
                            {reviewItems.recorded.map((item) => (
                                <li key={`${item.stage}-${item.code}`} className="p-2">
                                    <span className="font-medium">{t(item.label)}</span>: {t(RESULT_LABELS[item.result])}
                                    {item.comment && <span className="text-muted-foreground"> — {item.comment}</span>}
                                </li>
                            ))}
                        </ul>
                    </section>
                )}

                {snapshot && (
                    <section className="space-y-2">
                        <h2 className="font-semibold">{t('Submitted data')}</h2>
                        <Alert>
                            <AlertDescription>{t('This is the data as submitted. Review only from this copy.')}</AlertDescription>
                        </Alert>
                        <SnapshotSection value={snapshot} />
                    </section>
                )}

                {versions.length > 0 && (
                    <section className="space-y-2">
                        <h2 className="font-semibold">{t('Versions')}</h2>
                        {versions.map((version) => (
                            <div key={version.version_no} className="space-y-1 rounded-md border p-3 text-sm">
                                <p className="font-medium">
                                    {t('Version :number', { number: String(version.version_no) })} — {when(version.submitted_at)} — {version.declaration}
                                </p>
                                {version.response_note && <p>{version.response_note}</p>}
                                {version.changes.length > 0 && (
                                    <ul className="space-y-1">
                                        {version.changes.map((change) => (
                                            <li key={change.field} className="break-words">
                                                <span className="text-muted-foreground">{change.field}</span>: {show(change.old)} → {show(change.new)}
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>
                        ))}
                    </section>
                )}

                {inspections.length > 0 && (
                    <section className="space-y-2">
                        <h2 className="font-semibold">{t('Inspections')}</h2>
                        <ul className="divide-y rounded-md border text-sm">
                            {inspections.map((inspection) => (
                                <li key={inspection.id}>
                                    <Link href={inspectionShow(inspection.id)} className="flex flex-wrap items-center gap-2 p-2 hover:bg-muted">
                                        <span className="font-medium">{inspection.premises}</span>
                                        <span>{inspection.inspector}</span>
                                        <span className="text-muted-foreground">{t('Round :round', { round: String(inspection.round) })} — {inspection.planned_date}</span>
                                        <Badge variant="outline" className="ml-auto">
                                            {inspection.signed_at ? t('Signed') : inspection.status === 'CANCELLED' ? t('Cancelled') : inspection.conducted_at ? t('Visited') : t('Planned')}
                                        </Badge>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}

                {assignments.length > 0 && (
                    <section className="space-y-2">
                        <h2 className="font-semibold">{t('Assignments')}</h2>
                        <ul className="text-sm">
                            {assignments.map((assignment, index) => (
                                <li key={index}>
                                    {assignment.user} — {when(assignment.assigned_at)}
                                    {assignment.ended_at ? ` → ${when(assignment.ended_at)}` : ` (${t('open')})`}
                                </li>
                            ))}
                        </ul>
                    </section>
                )}

                <section className="space-y-2">
                    <h2 className="font-semibold">{t('Timeline')}</h2>
                    <ol className="space-y-2 text-sm">
                        {timeline.map((entry) => (
                            <li key={entry.id} className="border-l-2 pl-3">
                                <span className="text-muted-foreground">{when(entry.at)}</span> — {entry.action} ({entry.from} → {entry.to}) — {entry.actor}
                                {entry.on_behalf_of && ` ${t('for :name', { name: entry.on_behalf_of })}`}
                                {entry.comment && <p className="mt-1">{entry.comment}</p>}
                            </li>
                        ))}
                    </ol>
                </section>
            </div>
        </AdminLayout>
    );
}
