<?php

declare(strict_types=1);

namespace App\Domain\Documents\Services;

use App\Models\StoredFile;
use DateTimeInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LogicException;

/**
 * The only way to write or read files (D-056, 08 §4.2 rule 3). Every file gets
 * a new random key in the private R2 bucket and is never overwritten; a
 * replacement is a new StoredFile. Downloads use short-lived signed URLs,
 * issued only after the caller has checked permission.
 */
class FileStorage
{
    public const DISK = 'r2';

    public const SIGNED_URL_MINUTES = 5;

    public function store(UploadedFile $file, string $purpose, ?Model $uploadedBy = null): StoredFile
    {
        return $this->storeContents(
            (string) file_get_contents($file->getRealPath()),
            $file->getClientOriginalName(),
            $purpose,
            $uploadedBy,
        );
    }

    public function storeContents(string $contents, string $originalName, string $purpose, ?Model $uploadedBy = null): StoredFile
    {
        $path = $this->newPath($purpose);

        if (! $this->disk()->put($path, $contents)) {
            throw new LogicException("Could not write [{$path}] to the file store.");
        }

        return StoredFile::query()->create([
            'disk' => self::DISK,
            'path' => $path,
            'original_name' => $originalName,
            'mime_type' => (string) (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents),
            'size_bytes' => strlen($contents),
            'sha256' => hash('sha256', $contents),
            'uploaded_by_type' => $uploadedBy?->getMorphClass(),
            'uploaded_by_id' => $uploadedBy?->getKey(),
            'purpose' => $purpose,
        ]);
    }

    public function contents(StoredFile $file): string
    {
        $contents = (string) $this->disk()->get($file->path);

        if (! hash_equals($file->sha256, hash('sha256', $contents))) {
            throw new LogicException("Stored file [{$file->id}] failed its integrity check.");
        }

        return $contents;
    }

    /**
     * A 5-minute signed download URL. Call only after a permission check.
     */
    public function temporaryUrl(StoredFile $file, ?DateTimeInterface $expiresAt = null): string
    {
        return $this->disk()->temporaryUrl($file->path, $expiresAt ?? now()->addMinutes(self::SIGNED_URL_MINUTES), [
            'ResponseContentDisposition' => 'attachment; filename="'.addcslashes($file->original_name, '"\\').'"',
            'ResponseContentType' => $file->mime_type,
        ]);
    }

    private function newPath(string $purpose): string
    {
        do {
            $path = sprintf('%s/%s/%s', Str::slug(strtolower($purpose)), now()->format('Y/m'), Str::lower(Str::random(40)));
        } while (StoredFile::query()->where('path', $path)->exists() || $this->disk()->exists($path));

        return $path;
    }

    private function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }
}
