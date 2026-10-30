<?php

declare(strict_types=1);

use App\Domain\CompanyRegistry\Contracts\SubmissionState;
use App\Domain\CompanyRegistry\Enums\ChecklistItem;
use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\CompanyRegistry\Support\ProfileCompleteness;
use App\Domain\Identity\Enums\StaffRole;
use App\Domain\Notifications\Mail\CertificateUpdated;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Document;
use App\Models\FacilityChecklistItem;
use App\Models\FacilityChecklistPhoto;
use App\Models\Person;
use App\Models\Premises;
use App\Models\Region;
use App\Models\StoredFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

/*
| Premises, photos, documents and upload security (02 §4.4, 03 §12.2,
| §15 item 10, 05 §4.3–§4.4; D-017, D-067, D-073, D-089, D-135).
*/

beforeEach(function () {
    Storage::fake('r2');
    Mail::fake();
});

function pdfFile(string $body = '', string $name = 'certificate.pdf'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n{$body}\ntrailer << /Root 1 0 R >>\n%%EOF\n");
}

function pngFile(string $name = 'photo.png'): UploadedFile
{
    $image = imagecreatetruecolor(40, 30);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 100, 50));
    ob_start();
    imagepng($image);

    return UploadedFile::fake()->createWithContent($name, (string) ob_get_clean());
}

/**
 * A JPEG with an EXIF segment holding a marker text.
 */
function jpegWithExif(string $marker): UploadedFile
{
    $image = imagecreatetruecolor(40, 30);
    ob_start();
    imagejpeg($image);
    $jpeg = (string) ob_get_clean();
    $payload = "Exif\0\0".$marker;
    $segment = "\xFF\xE1".pack('n', strlen($payload) + 2).$payload;

    return UploadedFile::fake()->createWithContent('photo.jpg', substr($jpeg, 0, 2).$segment.substr($jpeg, 2));
}

function premisesFor(CompanyUser $account): Premises
{
    test()->actingAs($account, 'company')->post(route('portal.premises.store'), [
        'label' => 'Main warehouse',
        'premises_type' => 'WAREHOUSE',
        'address_fms_id' => ($region = Region::factory()->create())->federal_member_state_id,
        'address_region_id' => $region->id,
        'address_town_or_market' => 'Hodan',
    ])->assertSessionHasNoErrors();

    return Premises::query()->withoutGlobalScopes()->where('company_id', $account->company_id)->latest('id')->firstOrFail();
}

function uploadDocument(CompanyUser $account, array $data): TestResponse
{
    return test()->actingAs($account, 'company')->post(route('portal.documents.store'), $data);
}

/*
| Premises and checklist
*/

it('creates a premises with all 9 checklist items', function () {
    $premises = premisesFor(companyUser());

    expect($premises->checklistItems()->count())->toBe(count(ChecklistItem::cases()));
});

it('refuses GPS outside Somalia', function () {
    $account = companyUser();
    $region = Region::factory()->create();

    $this->actingAs($account, 'company')->post(route('portal.premises.store'), [
        'label' => 'Warehouse',
        'premises_type' => 'WAREHOUSE',
        'address_fms_id' => $region->federal_member_state_id,
        'address_region_id' => $region->id,
        'address_town_or_market' => 'Hodan',
        'gps_lat' => '25.2',
        'gps_lng' => '55.3',
    ])->assertSessionHasErrors(['gps_lat', 'gps_lng']);
});

it('adds re-encoded photos, at most 5 per item, and keeps the file when one is removed', function () {
    $account = companyUser();
    $premises = premisesFor($account);
    $item = $premises->checklistItems()->first();

    foreach (range(1, 5) as $i) {
        $this->actingAs($account, 'company')
            ->post(route('portal.premises.photos.store', [$premises, $item]), ['photo' => pngFile(), 'captured_at' => now()->subDay()->toIso8601String()])
            ->assertSessionHasNoErrors();
    }

    $this->actingAs($account, 'company')
        ->post(route('portal.premises.photos.store', [$premises, $item]), ['photo' => pngFile()])
        ->assertSessionHasErrors('photo');

    $photo = $item->photos()->first();
    $this->actingAs($account, 'company')->delete(route('portal.premises.photos.destroy', [$premises, $photo]))->assertRedirect();

    expect($item->photos()->count())->toBe(4)
        ->and(FacilityChecklistPhoto::withTrashed()->find($photo->id)->trashed())->toBeTrue()
        ->and(StoredFile::query()->find($photo->stored_file_id))->not->toBeNull();
});

it('removes EXIF data from photos (D-089)', function () {
    $account = companyUser();
    $premises = premisesFor($account);
    $item = $premises->checklistItems()->first();

    $this->actingAs($account, 'company')
        ->post(route('portal.premises.photos.store', [$premises, $item]), ['photo' => jpegWithExif('SECRET-LOCATION')])
        ->assertSessionHasNoErrors();

    $stored = StoredFile::query()->sole();
    expect(Storage::disk('r2')->get($stored->path))->not->toContain('SECRET-LOCATION')
        ->and($stored->mime_type)->toBe('image/jpeg');
});

it('accepts only images as checklist photos', function () {
    $account = companyUser();
    $premises = premisesFor($account);
    $item = $premises->checklistItems()->first();

    $this->actingAs($account, 'company')
        ->post(route('portal.premises.photos.store', [$premises, $item]), ['photo' => pdfFile()])
        ->assertSessionHasErrors('photo');
});

it('updates a checklist answer', function () {
    $account = companyUser();
    $premises = premisesFor($account);
    $item = $premises->checklistItems()->first();

    $this->actingAs($account, 'company')
        ->put(route('portal.premises.checklist.update', [$premises, $item]), ['is_available' => '1', 'notes' => 'Two extinguishers'])
        ->assertSessionHasNoErrors();

    expect($item->fresh()->is_available)->toBeTrue();
});

/*
| Upload security (03 §15 item 10)
*/

it('refuses unsafe files', function (Closure $file, string $field) {
    uploadDocument(companyUser(), ['document_type' => 'COMPANY_PROFILE', 'file' => $file()])
        ->assertSessionHasErrors($field);

    expect(StoredFile::query()->count())->toBe(0);
})->with([
    'renamed executable' => [fn () => UploadedFile::fake()->createWithContent('profile.pdf', "MZ\x90\x00\x03\x00\x00\x00\x04\x00\x00\x00\xFF\xFF\x00\x00".str_repeat("\x00", 200).'This program cannot be run in DOS mode'), 'file'],
    'oversized file' => [fn () => UploadedFile::fake()->create('profile.pdf', 6000, 'application/pdf'), 'file'],
    'PDF with JavaScript' => [fn () => pdfFile('2 0 obj << /OpenAction << /S /JavaScript /JS (app.alert(1)) >> >> endobj'), 'file'],
    'PDF with JavaScript in a compressed stream' => [fn () => pdfFile("3 0 obj << /Filter /FlateDecode >>\nstream\n".gzcompress('<< /S /JavaScript /JS (app.alert(1)) >>')."\nendstream\nendobj"), 'file'],
    'encrypted PDF' => [fn () => pdfFile('trailer << /Encrypt 5 0 R >>'), 'file'],
    'corrupt image' => [fn () => UploadedFile::fake()->createWithContent('scan.png', "\x89PNG\r\n\x1a\n".str_repeat('garbage', 20)), 'file'],
    'text file' => [fn () => UploadedFile::fake()->createWithContent('notes.pdf', 'just some text'), 'file'],
]);

it('accepts a clean PDF, JPG and PNG', function (Closure $file) {
    uploadDocument(companyUser(), ['document_type' => 'COMPANY_PROFILE', 'file' => $file()])
        ->assertSessionHasNoErrors();

    expect(StoredFile::query()->count())->toBe(1);
})->with([
    'pdf' => [fn () => pdfFile()],
    'jpg' => [fn () => jpegWithExif('x')],
    'png' => [fn () => pngFile()],
]);

/*
| Documents with history (D-067) and certificates (D-017)
*/

it('replaces the current document of a type and keeps the history', function () {
    $account = companyUser();

    uploadDocument($account, ['document_type' => 'COMPANY_PROFILE', 'file' => pdfFile()])->assertSessionHasNoErrors();
    uploadDocument($account, ['document_type' => 'COMPANY_PROFILE', 'file' => pdfFile('% v2')])->assertSessionHasNoErrors();

    $documents = Document::query()->withoutGlobalScopes()->orderBy('id')->get();
    expect($documents)->toHaveCount(2)
        ->and($documents[0]->replaced_by_id)->toBe($documents[1]->id)
        ->and($documents[1]->replaced_by_id)->toBeNull();
});

it('requires number and dates for certificates, with expiry after issue', function () {
    $account = companyUser();

    uploadDocument($account, ['document_type' => 'TAX_COMPLIANCE_CERTIFICATE', 'file' => pdfFile()])
        ->assertSessionHasErrors(['document_number', 'issued_on', 'expires_on']);

    uploadDocument($account, [
        'document_type' => 'TAX_COMPLIANCE_CERTIFICATE',
        'file' => pdfFile(),
        'document_number' => 'tx-1',
        'issued_on' => now()->subYear()->toDateString(),
        'expires_on' => now()->subYears(2)->toDateString(),
    ])->assertSessionHasErrors('expires_on');
});

it('tells the active Registrars when a certificate is renewed, not on the first upload', function () {
    $account = companyUser();
    $registrar = staffUser([StaffRole::PesticideRegistrar->value]);
    $inactiveRegistrar = staffUser([StaffRole::PesticideRegistrar->value], ['status' => 'INACTIVE']);
    staffUser([StaffRole::Inspector->value]);
    $certificate = fn () => [
        'document_type' => 'COMMERCE_REGISTRATION_CERTIFICATE',
        'file' => pdfFile(),
        'document_number' => 'CR-1',
        'issued_on' => now()->subMonth()->toDateString(),
        'expires_on' => now()->addYear()->toDateString(),
    ];

    uploadDocument($account, $certificate())->assertSessionHasNoErrors();
    Mail::assertNotQueued(CertificateUpdated::class);

    uploadDocument($account, $certificate())->assertSessionHasNoErrors();
    Mail::assertQueued(CertificateUpdated::class, 1);
    Mail::assertQueued(CertificateUpdated::class, fn ($mail) => $mail->hasTo($registrar->email));
    Mail::assertNotQueued(CertificateUpdated::class, fn ($mail) => $mail->hasTo($inactiveRegistrar->email));
});

it('links degree certificates and CVs to a person of the same company', function () {
    $account = companyUser();
    $person = Person::factory()->create(['company_id' => $account->company_id]);
    $foreign = Person::factory()->create();

    uploadDocument($account, ['document_type' => 'CV', 'file' => pdfFile(), 'person_id' => $person->id])->assertSessionHasNoErrors();
    uploadDocument($account, ['document_type' => 'CV', 'file' => pdfFile(), 'person_id' => $foreign->id])->assertSessionHasErrors('person_id');
});

it('downloads own files as attachments', function () {
    $account = companyUser();
    uploadDocument($account, ['document_type' => 'COMPANY_PROFILE', 'file' => pdfFile()]);
    $document = Document::query()->withoutGlobalScopes()->sole();
    Storage::disk('r2')->buildTemporaryUrlsUsing(fn (string $path, $expiration, array $options) => 'https://files.test/'.$path.'?d='.urlencode($options['ResponseContentDisposition']));

    $response = $this->actingAs($account, 'company')->get(route('portal.files.document', $document));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('attachment');
});

/*
| Profile completeness (D-178)
*/

it('marks the profile complete once everything is there', function () {
    $account = companyUser();
    $company = Company::query()->withoutGlobalScopes()->findOrFail($account->company_id);
    $region = Region::factory()->create();
    $address = ['address_fms_id' => $region->federal_member_state_id, 'address_region_id' => $region->id, 'address_town_or_market' => 'Hodan'];

    $this->actingAs($account, 'company')->post(route('portal.persons.store'), [
        'full_name' => 'Owner Person', 'national_id_no' => '11122233344', 'nationality' => 'SO',
        'email' => 'owner@example.com', 'phone' => '+252611111111', ...$address,
    ])->assertSessionHasNoErrors();
    $owner = Person::query()->withoutGlobalScopes()->sole();

    $this->actingAs($account, 'company')->put(route('portal.company.update'), [
        'name' => $company->name, 'business_registration_no' => $company->business_registration_no,
        'tax_compliance_no' => 'TX-1', 'email' => 'info@example.com', 'phone' => '+252612222222',
        'owner_person_id' => $owner->id, ...$address,
    ])->assertSessionHasNoErrors();

    foreach (DocumentType::cases() as $type) {
        if (! in_array($type, ProfileCompleteness::REQUIRED_DOCUMENTS, true)) {
            continue;
        }

        uploadDocument($account, [
            'document_type' => $type->value,
            'file' => pdfFile(),
            ...($type->isCertificate() ? ['document_number' => 'N-1', 'issued_on' => now()->subMonth()->toDateString(), 'expires_on' => now()->addYear()->toDateString()] : []),
        ])->assertSessionHasNoErrors();
    }

    expect($company->fresh()->profile_completed_at)->not->toBeNull();
});

/*
| Edit lock (D-073), ready for Phase 4
*/

it('refuses every profile change while locked, except certificate renewal', function () {
    $account = companyUser();
    $premises = premisesFor($account);
    $item = $premises->checklistItems()->first();
    $person = Person::factory()->create(['company_id' => $account->company_id]);
    $company = Company::query()->withoutGlobalScopes()->findOrFail($account->company_id);

    app()->bind(SubmissionState::class, fn () => new class implements SubmissionState
    {
        public function profileIsUnlocked(Company $company): bool
        {
            return false;
        }
    });

    $as = fn () => $this->actingAs($account, 'company');
    $as()->put(route('portal.company.update'), ['name' => 'Changed', 'business_registration_no' => $company->business_registration_no])->assertForbidden();
    $as()->post(route('portal.persons.store'), ['full_name' => 'New Person', 'national_id_no' => '22233344455'])->assertForbidden();
    $as()->put(route('portal.persons.update', $person), ['full_name' => 'Changed', 'national_id_no' => $person->national_id_no])->assertForbidden();
    $as()->post(route('portal.premises.store'), ['label' => 'X', 'premises_type' => 'SHOP'])->assertStatus(302); // validation runs first
    $as()->put(route('portal.premises.update', $premises), ['label' => 'Changed', 'premises_type' => 'SHOP', 'address_fms_id' => $premises->address_fms_id, 'address_region_id' => $premises->address_region_id, 'address_town_or_market' => 'Hodan'])->assertForbidden();
    $as()->put(route('portal.premises.checklist.update', [$premises, $item]), ['is_available' => '1'])->assertForbidden();
    $as()->post(route('portal.premises.photos.store', [$premises, $item]), ['photo' => pngFile()])->assertForbidden();
    uploadDocument($account, ['document_type' => 'COMPANY_PROFILE', 'file' => pdfFile()])->assertForbidden();

    uploadDocument($account, [
        'document_type' => 'TAX_COMPLIANCE_CERTIFICATE',
        'file' => pdfFile(),
        'document_number' => 'TX-2',
        'issued_on' => now()->subMonth()->toDateString(),
        'expires_on' => now()->addYear()->toDateString(),
    ])->assertSessionHasNoErrors();

    expect($company->fresh()->name)->not->toBe('Changed')
        ->and(FacilityChecklistItem::query()->find($item->id)->is_available)->toBeFalse()
        ->and(Document::query()->withoutGlobalScopes()->count())->toBe(1);
});
