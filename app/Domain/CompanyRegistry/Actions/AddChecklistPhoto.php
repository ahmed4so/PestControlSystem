<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Actions;

use App\Domain\CompanyRegistry\Enums\ChecklistItem;
use App\Domain\Documents\Services\SafeUploads;
use App\Models\CompanyUser;
use App\Models\FacilityChecklistItem;
use App\Models\FacilityChecklistPhoto;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Adds a photo to a checklist item: at most 5 per item (D-135), JPG or
 * PNG only, re-encoded on the server (D-089).
 */
final class AddChecklistPhoto
{
    public const PURPOSE = 'FACILITY_PHOTO';

    public function __construct(private readonly SafeUploads $uploads) {}

    public function __invoke(FacilityChecklistItem $item, UploadedFile $photo, ?CarbonImmutable $capturedAt, CompanyUser $uploadedBy): FacilityChecklistPhoto
    {
        if ($item->photos()->count() >= ChecklistItem::MAX_PHOTOS) {
            throw ValidationException::withMessages(['photo' => __('An item can have at most 5 photos. Remove one first.')]);
        }

        $file = $this->uploads->store($photo, self::PURPOSE, $uploadedBy, SafeUploads::PHOTO_TYPES);

        return DB::transaction(fn () => $item->photos()->create([
            'stored_file_id' => $file->id,
            'captured_at' => $capturedAt,
        ]));
    }
}
