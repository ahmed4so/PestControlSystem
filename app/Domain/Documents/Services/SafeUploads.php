<?php

declare(strict_types=1);

namespace App\Domain\Documents\Services;

use App\Domain\Documents\Support\ImageReencoder;
use App\Domain\Documents\Support\UploadInspector;
use App\Models\StoredFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * The way company uploads enter the file store (03 §12.2, D-089): check
 * the content, re-encode images, then store under a random key. The
 * original name is kept only as metadata.
 */
final class SafeUploads
{
    public const PHOTO_TYPES = ['image/jpeg', 'image/png'];

    public function __construct(
        private readonly UploadInspector $inspector,
        private readonly ImageReencoder $reencoder,
        private readonly FileStorage $files,
    ) {}

    /**
     * @param  list<string>|null  $allowed  MIME types; defaults to PDF, JPG, PNG
     */
    public function store(UploadedFile $file, string $purpose, Model $uploadedBy, ?array $allowed = null): StoredFile
    {
        $mime = $this->inspector->inspect($file, $allowed);
        $contents = (string) file_get_contents($file->getRealPath());

        if (in_array($mime, self::PHOTO_TYPES, true)) {
            ['contents' => $contents, 'mime' => $mime] = $this->reencoder->reencode($contents, $mime);
        }

        return $this->files->storeContents($contents, $this->safeName($file, $mime), $purpose, $uploadedBy);
    }

    private function safeName(UploadedFile $file, string $mime): string
    {
        $base = Str::limit(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file', 100, '');
        $extension = match ($mime) {
            'application/pdf' => 'pdf',
            'image/png' => 'png',
            default => 'jpg',
        };

        return "{$base}.{$extension}";
    }
}
