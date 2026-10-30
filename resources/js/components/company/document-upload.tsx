import { Form } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/lib/i18n';
import { store } from '@/routes/portal/documents';

/**
 * Uploads one document (05 §4.4). Certificates also ask for the number
 * and dates (D-017).
 */
export default function DocumentUpload({
    type,
    isCertificate = false,
    personId,
}: {
    type: string;
    isCertificate?: boolean;
    personId?: number;
}) {
    const { t } = useTranslation();
    const id = (field: string) => `${type}-${personId ?? 'company'}-${field}`;

    return (
        <Form {...store.form()} options={{ preserveScroll: true }} resetOnSuccess className="space-y-3">
            {({ processing, errors, progress }) => (
                <>
                    <input type="hidden" name="document_type" value={type} />
                    {personId && <input type="hidden" name="person_id" value={personId} />}

                    {isCertificate && (
                        <div className="grid gap-3 sm:grid-cols-3">
                            <div className="grid gap-1">
                                <Label htmlFor={id('number')}>{t('Certificate number')}</Label>
                                <Input id={id('number')} name="document_number" maxLength={60} required />
                                <InputError message={errors.document_number} />
                            </div>
                            <div className="grid gap-1">
                                <Label htmlFor={id('issued')}>{t('Issued on')}</Label>
                                <Input id={id('issued')} name="issued_on" type="date" required />
                                <InputError message={errors.issued_on} />
                            </div>
                            <div className="grid gap-1">
                                <Label htmlFor={id('expires')}>{t('Expires on')}</Label>
                                <Input id={id('expires')} name="expires_on" type="date" required />
                                <InputError message={errors.expires_on} />
                            </div>
                        </div>
                    )}

                    <div className="grid gap-1">
                        <Label htmlFor={id('file')}>{t('File (PDF, JPG or PNG, up to 5 MB)')}</Label>
                        <Input id={id('file')} name="file" type="file" accept="application/pdf,image/jpeg,image/png" required />
                        <InputError message={errors.file ?? errors.document_type ?? errors.person_id} />
                    </div>

                    <Button type="submit" size="sm" disabled={processing}>
                        {progress ? t('Uploading… :percent%', { percent: progress.percentage ?? 0 }) : t('Upload')}
                    </Button>
                </>
            )}
        </Form>
    );
}
