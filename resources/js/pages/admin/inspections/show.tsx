import { Form, Head, Link, router } from '@inertiajs/react';
import { Camera, MapPin, Video } from 'lucide-react';
import { useRef, useState } from 'react';
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
import { compressImage } from '@/lib/compress-image';
import { useTranslation } from '@/lib/i18n';
import { queues } from '@/routes/admin';
import { show as applicationShow } from '@/routes/admin/applications';
import { show as inspectionShow, media as mediaRoute, report, reschedule, sign, visit } from '@/routes/admin/inspections';

type Inspection = {
    id: number;
    application_id: number;
    reference: string | null;
    application_status: string;
    round: number;
    planned_date: string;
    conducted_at: string | null;
    signed_at: string | null;
    status: string;
    inspector: string | null;
    gps_lat: string | null;
    gps_lng: string | null;
    gps_unavailable_reason: string | null;
    observed_facts: string | null;
    recommendations: string | null;
    deficiencies: string | null;
    overall_result: string | null;
};
type ChecklistItem = { code: string; label: string; result: string | null; note: string | null; photos: number };
type Props = {
    inspection: Inspection;
    premises: { label?: string; premises_type?: string; address?: Record<string, string | null>; gps_lat?: string | null; gps_lng?: string | null } | null;
    checklist: ChecklistItem[];
    media: { id: number; type: string; item: string | null; captured_at: string | null }[];
    can: { visit: boolean; edit: boolean; sign: boolean; reschedule: boolean };
    maxPhotos: number;
};

const RESULTS: Record<string, string> = { COMPLIANT: 'Compliant', NON_COMPLIANT: 'Non-compliant', NOT_APPLICABLE: 'Not applicable' };
const OVERALL: Record<string, string> = { SATISFACTORY: 'Satisfactory', DEFICIENCIES_FOUND: 'Deficiencies found' };

/**
 * Uploads one photo (compressed on the phone) or one MP4 video on its own,
 * so a slow connection only re-sends that file (D-135).
 */
function MediaUpload({ inspectionId, type, itemCode, gps, disabled }: { inspectionId: number; type: 'PHOTO' | 'VIDEO'; itemCode?: string; gps: { lat: string; lng: string }; disabled: boolean }) {
    const { t } = useTranslation();
    const input = useRef<HTMLInputElement>(null);
    const [progress, setProgress] = useState<number | null>(null);
    const [error, setError] = useState<string | undefined>();

    const upload = async (file: File | undefined) => {
        if (!file) {
            return;
        }

        setError(undefined);
        const prepared = type === 'PHOTO' ? await compressImage(file) : file;

        router.post(
            mediaRoute.url(inspectionId),
            { file: prepared, type, item_code: itemCode ?? null, gps_lat: gps.lat || null, gps_lng: gps.lng || null },
            {
                forceFormData: true,
                preserveScroll: true,
                onProgress: (event) => setProgress(event?.percentage ?? null),
                onError: (errors) => setError(errors.file ?? errors.inspection ?? errors.type),
                onFinish: () => {
                    setProgress(null);
                    if (input.current) {
                        input.current.value = '';
                    }
                },
            },
        );
    };

    return (
        <div className="space-y-1">
            <input
                ref={input}
                type="file"
                accept={type === 'PHOTO' ? 'image/jpeg,image/png' : 'video/mp4'}
                capture="environment"
                className="hidden"
                onChange={(event) => upload(event.target.files?.[0])}
            />
            <Button type="button" variant="outline" size="sm" disabled={disabled || progress !== null} onClick={() => input.current?.click()}>
                {type === 'PHOTO' ? <Camera /> : <Video />}
                {progress !== null ? t('Uploading… :percent%', { percent: String(progress) }) : type === 'PHOTO' ? t('Add photo') : t('Add video (MP4, up to 50 MB)')}
            </Button>
            <InputError message={error} />
        </div>
    );
}

/**
 * The inspection of one premises, made for a phone at the premises
 * (procedure 6.5–6.7, D-077, D-136): record the visit, fill the checklist
 * (including the four SOPs), photos, video and GPS, then sign the report,
 * which locks it.
 */
export default function InspectionShow({ inspection, premises, checklist, media, can, maxPhotos }: Props) {
    const { t } = useTranslation();
    const [gps, setGps] = useState({ lat: inspection.gps_lat ?? '', lng: inspection.gps_lng ?? '' });
    const [gpsMessage, setGpsMessage] = useState<string | null>(null);
    const locked = inspection.signed_at !== null;

    const capture = () => {
        if (!('geolocation' in navigator)) {
            setGpsMessage(t('This device cannot give its position. Write the reason below.'));
            return;
        }

        setGpsMessage(t('Finding your position…'));
        navigator.geolocation.getCurrentPosition(
            (position) => {
                setGps({ lat: position.coords.latitude.toFixed(7), lng: position.coords.longitude.toFixed(7) });
                setGpsMessage(null);
            },
            () => setGpsMessage(t('The position is not available. Write the reason below.')),
            { enableHighAccuracy: true, timeout: 20000 },
        );
    };

    return (
        <AdminLayout
            breadcrumbs={[
                { title: t('Work queues'), href: queues().url },
                { title: inspection.reference ?? t('Application'), href: applicationShow(inspection.application_id).url },
                { title: t('Inspection'), href: inspectionShow(inspection.id).url },
            ]}
        >
            <Head title={t('Inspection')} />

            <div className="mx-auto max-w-2xl space-y-6 p-4">
                <div className="space-y-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <h1 className="text-xl font-semibold">{premises?.label ?? t('Inspection')}</h1>
                        <Badge variant={locked ? 'secondary' : 'outline'}>{locked ? t('Signed') : inspection.conducted_at ? t('Visited') : t('Planned')}</Badge>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        <Link href={applicationShow(inspection.application_id)} className="underline">
                            {inspection.reference}
                        </Link>{' '}
                        — {t('Planned for :date', { date: inspection.planned_date })} — {inspection.inspector}
                    </p>
                    {premises?.address && (
                        <p className="text-sm">{[premises.address.town_or_market, premises.address.district, premises.address.region_name_en].filter(Boolean).join(', ')}</p>
                    )}
                </div>

                {can.visit && (
                    <Form {...visit.form(inspection.id)} options={{ preserveScroll: true }}>
                        {({ processing, errors }) => (
                            <div className="space-y-2">
                                <Button type="submit" disabled={processing} className="w-full sm:w-auto">
                                    {processing && <Spinner />}
                                    {t('I am at the premises: record the visit')}
                                </Button>
                                <InputError message={errors.inspection} />
                            </div>
                        )}
                    </Form>
                )}

                {can.reschedule && (
                    <Form {...reschedule.form(inspection.id)} options={{ preserveScroll: true }} className="space-y-2 rounded-md border p-3">
                        {({ processing, errors }) => (
                            <>
                                <h2 className="font-semibold">{t('Move the inspection')}</h2>
                                <Input type="date" name="planned_date" defaultValue={inspection.planned_date} required />
                                <Textarea name="comment" required maxLength={2000} placeholder={t('Reason for the new date')} />
                                <InputError message={errors.planned_date ?? errors.comment ?? errors.inspection ?? errors.action} />
                                <Button type="submit" variant="outline" disabled={processing}>
                                    {processing && <Spinner />}
                                    {t('Move the inspection')}
                                </Button>
                            </>
                        )}
                    </Form>
                )}

                {(can.edit || locked || inspection.conducted_at) && (
                    <Form {...report.form(inspection.id)} options={{ preserveScroll: true }} className="space-y-4">
                        {({ processing, errors }) => (
                            <fieldset disabled={!can.edit} className="space-y-4">
                                <h2 className="font-semibold">{t('Checklist')}</h2>
                                {checklist.map((item) => (
                                    <div key={item.code} className="space-y-2 rounded-md border p-3">
                                        <Label htmlFor={`item-${item.code}`}>{t(item.label)}</Label>
                                        <NativeSelect id={`item-${item.code}`} name={`items[${item.code}][result]`} defaultValue={item.result ?? ''}>
                                            <option value="">{t('Choose…')}</option>
                                            {Object.entries(RESULTS).map(([value, label]) => (
                                                <option key={value} value={value}>
                                                    {t(label)}
                                                </option>
                                            ))}
                                        </NativeSelect>
                                        <Textarea name={`items[${item.code}][note]`} defaultValue={item.note ?? ''} maxLength={2000} placeholder={t('Note')} rows={2} />
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span className="text-xs text-muted-foreground">{t(':count of :max photos', { count: String(item.photos), max: String(maxPhotos) })}</span>
                                            {can.edit && item.result && (
                                                <MediaUpload inspectionId={inspection.id} type="PHOTO" itemCode={item.code} gps={gps} disabled={item.photos >= maxPhotos} />
                                            )}
                                        </div>
                                    </div>
                                ))}

                                <div className="grid gap-2">
                                    <Label htmlFor="observed_facts">{t('Observed facts')}</Label>
                                    <Textarea id="observed_facts" name="observed_facts" defaultValue={inspection.observed_facts ?? ''} maxLength={5000} rows={4} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="deficiencies">{t('Deficiencies (required if an item is non-compliant)')}</Label>
                                    <Textarea id="deficiencies" name="deficiencies" defaultValue={inspection.deficiencies ?? ''} maxLength={5000} rows={3} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="recommendations">{t('Recommendations')}</Label>
                                    <Textarea id="recommendations" name="recommendations" defaultValue={inspection.recommendations ?? ''} maxLength={5000} rows={3} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="overall_result">{t('Overall result')}</Label>
                                    <NativeSelect id="overall_result" name="overall_result" defaultValue={inspection.overall_result ?? ''}>
                                        <option value="">{t('Choose…')}</option>
                                        {Object.entries(OVERALL).map(([value, label]) => (
                                            <option key={value} value={value}>
                                                {t(label)}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                </div>

                                <div className="space-y-2 rounded-md border p-3">
                                    <Label>{t('GPS position')}</Label>
                                    <input type="hidden" name="gps_lat" value={gps.lat} />
                                    <input type="hidden" name="gps_lng" value={gps.lng} />
                                    <p className="text-sm">{gps.lat && gps.lng ? `${gps.lat}, ${gps.lng}` : t('Not recorded')}</p>
                                    {can.edit && (
                                        <Button type="button" variant="outline" size="sm" onClick={capture}>
                                            <MapPin />
                                            {t('Record my position')}
                                        </Button>
                                    )}
                                    {gpsMessage && <p className="text-sm text-muted-foreground">{gpsMessage}</p>}
                                    <Input name="gps_unavailable_reason" defaultValue={inspection.gps_unavailable_reason ?? ''} maxLength={255} placeholder={t('Reason, if the position is not available')} />
                                    <InputError message={errors.gps_lat ?? errors.gps_lng} />
                                </div>

                                <InputError message={errors.inspection ?? errors.items} />
                                {can.edit && (
                                    <Button type="submit" disabled={processing}>
                                        {processing && <Spinner />}
                                        {t('Save the report')}
                                    </Button>
                                )}
                            </fieldset>
                        )}
                    </Form>
                )}

                {can.edit && (
                    <section className="space-y-2">
                        <h2 className="font-semibold">{t('Video (optional)')}</h2>
                        <MediaUpload inspectionId={inspection.id} type="VIDEO" gps={gps} disabled={false} />
                    </section>
                )}

                {media.length > 0 && (
                    <p className="text-sm text-muted-foreground">{t(':count photos and videos recorded', { count: String(media.length) })}</p>
                )}

                {can.sign && (
                    <Form {...sign.form(inspection.id)} options={{ preserveScroll: true }} resetOnError={['password']} className="space-y-2 rounded-md border p-3">
                        {({ processing, errors }) => (
                            <>
                                <h2 className="font-semibold">{t('Sign the report')}</h2>
                                <Alert>
                                    <AlertDescription>{t('Save the report first. Once signed, it can no longer be changed.')}</AlertDescription>
                                </Alert>
                                <Input name="password" type="password" autoComplete="current-password" required placeholder={t('Your password')} />
                                <InputError message={errors.password ?? errors.inspection} />
                                <Button type="submit" disabled={processing}>
                                    {processing && <Spinner />}
                                    {t('Sign the report')}
                                </Button>
                            </>
                        )}
                    </Form>
                )}
            </div>
        </AdminLayout>
    );
}
