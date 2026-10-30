<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Models\Company;
use App\Models\Document;
use App\Models\StoredFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'document_type' => DocumentType::CompanyProfile,
            'stored_file_id' => fn () => StoredFile::query()->create([
                'disk' => (string) config('documents.disk'),
                'path' => 'company-document/'.Str::lower(Str::random(40)),
                'original_name' => 'profile.pdf',
                'mime_type' => 'application/pdf',
                'size_bytes' => 100,
                'sha256' => str_repeat('0', 64),
                'purpose' => 'COMPANY_DOCUMENT',
            ])->id,
        ];
    }

    public function certificate(DocumentType $type, ?string $expiresOn = null): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => $type,
            'document_number' => strtoupper(Str::random(8)),
            'issued_on' => now()->subYear()->toDateString(),
            'expires_on' => $expiresOn ?? now()->addYear()->toDateString(),
        ]);
    }
}
