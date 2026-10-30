<?php

declare(strict_types=1);

use App\Domain\Identity\Enums\StaffRole;
use App\Models\AuditActivity;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Document;
use App\Models\FacilityChecklistItem;
use App\Models\FacilityChecklistPhoto;
use App\Models\FederalMemberState;
use App\Models\Person;
use App\Models\Premises;
use App\Models\Region;
use App\Models\StoredFile;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Storage;

/*
| A company can never see or change another company's records or files
| (03 §11.3, §15 item 3). Staff read company files with `view applications`
| and every file view is audited (D-177, D-181, 03 §13).
*/

beforeEach(fn () => Storage::fake('r2'));

/**
 * Records of a second company, with one of everything.
 *
 * @return array{account: CompanyUser, person: Person, premises: Premises, item: FacilityChecklistItem, photo: FacilityChecklistPhoto, document: Document}
 */
function otherCompanyRecords(): array
{
    $account = companyUser();
    $person = Person::factory()->create(['company_id' => $account->company_id]);
    $premises = Premises::factory()->create(['company_id' => $account->company_id]);
    $item = $premises->checklistItems()->create(['item_code' => 'VENTILATION', 'is_available' => true]);
    $file = StoredFile::query()->create([
        'disk' => 'r2', 'path' => 'facility-photo/x/'.uniqid(), 'original_name' => 'p.jpg', 'mime_type' => 'image/jpeg',
        'size_bytes' => 1, 'sha256' => str_repeat('0', 64), 'purpose' => 'FACILITY_PHOTO',
    ]);
    $photo = $item->photos()->create(['stored_file_id' => $file->id]);
    $document = Document::factory()->create(['company_id' => $account->company_id]);

    return compact('account', 'person', 'premises', 'item', 'photo', 'document');
}

it('gives 404 for every company route with another company\'s IDs', function () {
    $me = companyUser();
    $other = otherCompanyRecords();
    $ids = [
        'person' => $other['person']->id,
        'premises' => $other['premises']->id,
        'item' => $other['item']->id,
        'photo' => $other['photo']->id,
        'document' => $other['document']->id,
    ];
    $checked = 0;

    foreach (app('router')->getRoutes() as $route) {
        /** @var Route $route */
        $parameters = $route->parameterNames();

        if (! str_starts_with((string) $route->getName(), 'portal.') || $parameters === [] || $route->getName() === 'portal.email.confirm' || $route->getName() === 'portal.password.reset') {
            continue;
        }

        $url = '/'.ltrim(preg_replace_callback('/\{(\w+)\??\}/', fn ($m) => (string) $ids[$m[1]], $route->uri()), '/');

        foreach (array_diff($route->methods(), ['HEAD']) as $method) {
            $this->actingAs($me, 'company')->call($method, $url, ['full_name' => 'X', 'national_id_no' => '12345678901', 'label' => 'X', 'premises_type' => 'SHOP', 'is_available' => '1'])
                ->assertNotFound();
            $checked++;
        }
    }

    expect($checked)->toBeGreaterThanOrEqual(8)
        ->and($other['person']->fresh()->full_name)->not->toBe('X')
        ->and($other['premises']->fresh()->label)->not->toBe('X')
        ->and(FacilityChecklistPhoto::query()->find($other['photo']->id))->not->toBeNull();
});

it('never lists another company\'s records', function () {
    $me = companyUser();
    $other = otherCompanyRecords();

    $this->actingAs($me, 'company')
        ->get(route('portal.persons.index'))
        ->assertInertia(fn ($page) => $page->where('persons', []));

    $this->actingAs($me, 'company')
        ->get(route('portal.premises.index'))
        ->assertInertia(fn ($page) => $page->where('premises', []));

    $this->actingAs($me, 'company')
        ->get(route('portal.documents.index'))
        ->assertInertia(fn ($page) => $page->where('types', fn ($types) => collect($types)->every(fn ($type) => $type['current'] === null)));

    expect(Company::query()->withoutGlobalScopes()->count())->toBe(2);
});

it('lets staff with company-file access read the directory and file, with IDs masked', function () {
    $other = otherCompanyRecords();
    $registrar = staffUser([StaffRole::PesticideRegistrar->value]);

    $this->actingAs($registrar, 'web')->get(route('admin.companies.index'))->assertOk();
    $this->actingAs($registrar, 'web')
        ->get(route('admin.companies.show', $other['account']->company_id))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('persons.0.national_id', '****'.substr($other['person']->national_id_no, -4)))
        ->assertDontSee($other['person']->national_id_no);
});

it('refuses the directory to staff without the permission', function () {
    $other = otherCompanyRecords();

    $this->actingAs(systemAdministrator(), 'web')->get(route('admin.companies.index'))->assertForbidden();
    $this->actingAs(staffUser([StaffRole::TechnicalReviewer->value]), 'web')
        ->get(route('admin.companies.show', $other['account']->company_id))
        ->assertForbidden();
});

it('audits every staff file view', function () {
    $other = otherCompanyRecords();
    $registrar = staffUser([StaffRole::PesticideRegistrar->value]);
    Storage::disk('r2')->buildTemporaryUrlsUsing(fn (string $path) => 'https://files.test/'.$path);

    $this->actingAs($registrar, 'web')->get(route('admin.files.document', $other['document']))->assertRedirect();
    $this->actingAs($registrar, 'web')->get(route('admin.files.photo', $other['photo']))->assertRedirect();

    expect(AuditActivity::query()->where('event', 'company_file_viewed')->where('causer_id', $registrar->id)->count())->toBe(2);
});

it('lets only the System Administrator manage the FMS and region lists', function () {
    $this->actingAs(staffUser([StaffRole::PesticideRegistrar->value]), 'web')
        ->get(route('admin.reference-data.fms.index'))
        ->assertForbidden();

    $admin = systemAdministrator();
    $this->actingAs($admin, 'web')->get(route('admin.reference-data.fms.index'))->assertOk();
    $this->actingAs($admin, 'web')->post(route('admin.reference-data.fms.store'), [
        'code' => 'new_state', 'name_en' => 'New State', 'name_so' => 'Dowlad Cusub', 'sort_order' => 9, 'is_active' => '1',
    ])->assertSessionHasNoErrors();

    $fms = FederalMemberState::query()->where('code', 'NEW_STATE')->firstOrFail();
    $this->actingAs($admin, 'web')->post(route('admin.reference-data.regions.store'), [
        'federal_member_state_id' => $fms->id, 'name_en' => 'New Region', 'name_so' => 'Gobol Cusub', 'sort_order' => 0, 'is_active' => '1',
    ])->assertSessionHasNoErrors();

    expect($fms->regions()->count())->toBe(1);
});

it('seeds the draft FMS and region list (D-184)', function () {
    $this->seed(ReferenceDataSeeder::class);

    expect(FederalMemberState::query()->count())->toBe(6)
        ->and(Region::query()->count())->toBeGreaterThan(6);
});
