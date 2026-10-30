import { router } from '@inertiajs/react';
import { Camera } from 'lucide-react';
import { useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { compressImage } from '@/lib/compress-image';
import { useTranslation } from '@/lib/i18n';

/**
 * Takes or chooses one photo, compresses it on the phone and uploads it
 * alone, so a slow connection only re-sends one photo (D-135).
 */
export default function PhotoUpload({ url, disabled = false }: { url: string; disabled?: boolean }) {
    const { t } = useTranslation();
    const input = useRef<HTMLInputElement>(null);
    const [progress, setProgress] = useState<number | null>(null);
    const [error, setError] = useState<string | undefined>();

    const upload = async (file: File | undefined) => {
        if (!file) {
            return;
        }

        setError(undefined);
        const photo = await compressImage(file);

        router.post(
            url,
            {
                photo,
                captured_at: file.lastModified ? new Date(file.lastModified).toISOString() : null,
            },
            {
                forceFormData: true,
                preserveScroll: true,
                onProgress: (event) => setProgress(event?.percentage ?? null),
                onError: (errors) => setError(errors.photo ?? errors.captured_at),
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
                accept="image/jpeg,image/png"
                capture="environment"
                className="hidden"
                onChange={(event) => upload(event.target.files?.[0])}
            />
            <Button
                type="button"
                variant="outline"
                size="sm"
                disabled={disabled || progress !== null}
                onClick={() => input.current?.click()}
            >
                <Camera />
                {progress !== null ? t('Uploading… :percent%', { percent: progress }) : t('Add photo')}
            </Button>
            <InputError message={error} />
        </div>
    );
}
