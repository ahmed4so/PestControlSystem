<?php

declare(strict_types=1);

namespace App\Domain\Documents\Support;

use App\Domain\Documents\Exceptions\UnsafeUpload;
use finfo;
use Illuminate\Http\UploadedFile;

/**
 * Checks a company upload before it is stored (03 §12.2, D-017, D-089):
 * the type is detected from the content (not the name or the browser), the
 * size is limited, and PDFs that are encrypted or carry JavaScript or
 * automatic actions are refused, including inside compressed streams.
 */
final class UploadInspector
{
    /** PDF names that run code or actions when the file is opened. */
    private const PDF_ACTIVE_CONTENT = '#/(JavaScript|JS|OpenAction|AA|Launch|RichMedia|EmbeddedFile|XFA)\b#';

    /**
     * @param  list<string>|null  $allowed  MIME types; defaults to PDF, JPG, PNG
     * @return string the detected MIME type
     */
    public function inspect(UploadedFile $file, ?array $allowed = null): string
    {
        $allowed ??= (array) config('documents.allowed_mime_types');

        if (! $file->isValid()) {
            throw UnsafeUpload::type();
        }

        if ($file->getSize() > (int) config('documents.max_upload_kb') * 1024) {
            throw UnsafeUpload::size();
        }

        $contents = (string) file_get_contents($file->getRealPath());

        return $this->inspectContents($contents, $allowed);
    }

    /**
     * @param  list<string>  $allowed
     */
    public function inspectContents(string $contents, array $allowed): string
    {
        $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->buffer($contents);

        if (! in_array($mime, $allowed, true)) {
            throw UnsafeUpload::type();
        }

        if ($mime === 'application/pdf' && $this->pdfHasActiveContentOrEncryption($contents)) {
            throw UnsafeUpload::pdf();
        }

        return $mime;
    }

    private function pdfHasActiveContentOrEncryption(string $pdf): bool
    {
        if (preg_match('#/Encrypt\b#', $pdf) === 1 || preg_match(self::PDF_ACTIVE_CONTENT, $pdf) === 1) {
            return true;
        }

        // Names can hide inside compressed object streams; inflate them.
        if (preg_match_all('#stream\r?\n(.*?)\r?\nendstream#s', $pdf, $streams) > 0) {
            foreach ($streams[1] as $stream) {
                $inflated = @gzuncompress($stream);

                if (is_string($inflated) && (preg_match(self::PDF_ACTIVE_CONTENT, $inflated) === 1 || preg_match('#/Encrypt\b#', $inflated) === 1)) {
                    return true;
                }
            }
        }

        return false;
    }
}
