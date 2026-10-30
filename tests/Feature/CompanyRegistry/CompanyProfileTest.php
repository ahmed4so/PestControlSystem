<?php

declare(strict_types=1);

use App\Domain\CompanyRegistry\Actions\CreateCompany;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Person;
use App\Models\Region;

/*
| Company profile (02 §4.4, 05 §4.1, §4.5; D-076, D-178, D-182).
*/

it('sends an account without a company to profile set-up', function () {
    $account = companyUser(withCompany: false);

    foreach (['portal.dashboard', 'portal.persons.index', 'portal.premises.index', 'portal.documents.index', 'portal.company.edit'] as $route) {
        $this->actingAs($account, 'company')->get(route($route))->assertRedirect(route('portal.company.create'));
    }

    $this->actingAs($account, 'company')->get(route('portal.profile.edit'))->assertOk();
});

it('creates the company with a normalised registration number and links the account', function () {
    $account = companyUser(withCompany: false);

    $this->actingAs($account, 'company')
        ->post(route('portal.company.store'), ['name' => '  Hodan  Agro Supplies ', 'business_registration_no' => ' br-123 /45 '])
        ->assertRedirect(route('portal.company.edit'));

    $company = Company::query()->withoutGlobalScopes()->sole();
    expect($company->name)->toBe('Hodan Agro Supplies')
        ->and($company->business_registration_no)->toBe('BR-123/45')
        ->and($account->fresh()->company_id)->toBe($company->id);
});

it('refuses a registration number that is already registered (D-182)', function () {
    Company::factory()->create(['business_registration_no' => 'BR-777']);
    $account = companyUser(withCompany: false);

    $this->actingAs($account, 'company')
        ->post(route('portal.company.store'), ['name' => 'Another Company', 'business_registration_no' => 'br-777'])
        ->assertSessionHasErrors(['business_registration_no' => __(CreateCompany::DUPLICATE_MESSAGE)]);

    expect($account->fresh()->company_id)->toBeNull()
        ->and(Company::query()->withoutGlobalScopes()->count())->toBe(1);
});

it('creates only one company per account', function () {
    $account = companyUser();

    $this->actingAs($account, 'company')
        ->post(route('portal.company.store'), ['name' => 'Second', 'business_registration_no' => 'BR-999']);

    expect(Company::query()->withoutGlobalScopes()->count())->toBe(1);
});

it('saves the profile step by step, normalised, with a valid address', function () {
    $account = companyUser();
    $region = Region::factory()->create();

    $this->actingAs($account, 'company')->put(route('portal.company.update'), [
        'name' => 'Hodan Agro',
        'business_registration_no' => 'br-1',
        'tax_compliance_no' => ' tx 55 ',
        'email' => ' INFO@Hodan.SO ',
        'phone' => '0612345678',
        // Strings, as a browser form sends them.
        'address_fms_id' => (string) $region->federal_member_state_id,
        'address_region_id' => (string) $region->id,
        'address_town_or_market' => 'Bakara',
    ])->assertSessionHasNoErrors()->assertRedirect();

    $company = Company::query()->withoutGlobalScopes()->findOrFail($account->company_id);
    expect($company->tax_compliance_no)->toBe('TX55')
        ->and($company->email)->toBe('info@hodan.so')
        ->and($company->phone)->toBe('+252612345678')
        ->and($company->address_town_or_market)->toBe('Bakara')
        ->and($company->profile_completed_at)->toBeNull();
});

it('refuses a region of another FMS and inactive values (05 §4.5)', function () {
    $account = companyUser();
    $region = Region::factory()->create();
    $otherRegion = Region::factory()->create();
    $inactive = Region::factory()->create(['is_active' => false]);

    $this->actingAs($account, 'company')->put(route('portal.company.update'), [
        'name' => 'Hodan Agro',
        'business_registration_no' => 'BR-1',
        'address_fms_id' => $region->federal_member_state_id,
        'address_region_id' => $otherRegion->id,
    ])->assertSessionHasErrors('address_region_id');

    $this->actingAs($account, 'company')->put(route('portal.company.update'), [
        'name' => 'Hodan Agro',
        'business_registration_no' => 'BR-1',
        'address_fms_id' => $inactive->federal_member_state_id,
        'address_region_id' => $inactive->id,
    ])->assertSessionHasErrors('address_region_id');
});

it('accepts only an active person of the same company as Owner/MD', function () {
    $account = companyUser();
    $foreignPerson = Person::factory()->create();

    $this->actingAs($account, 'company')->put(route('portal.company.update'), [
        'name' => 'Hodan Agro',
        'business_registration_no' => 'BR-1',
        'owner_person_id' => $foreignPerson->id,
    ])->assertSessionHasErrors('owner_person_id');
});

it('shows what is still missing and marks the profile complete when nothing is (D-178)', function () {
    $account = companyUser();

    $this->actingAs($account, 'company')
        ->get(route('portal.dashboard'))
        ->assertInertia(fn ($page) => $page->where('missing', fn ($missing) => collect($missing)->contains('Owner / Managing Director')));
});

it('shares the FMS list, active values only', function () {
    $account = companyUser();
    Region::factory()->create(['name_en' => 'Active region']);
    Region::factory()->create(['name_en' => 'Hidden region', 'is_active' => false]);

    $this->actingAs($account, 'company')
        ->get(route('portal.company.edit'))
        ->assertInertia(fn ($page) => $page->where('fms', function ($fms) {
            $regions = collect($fms)->pluck('regions')->flatten(1)->pluck('name');

            return $regions->contains('Active region') && ! $regions->contains('Hidden region');
        }));
});

it('gives each account its own company only', function () {
    $mine = companyUser();
    $other = companyUser();

    $this->actingAs($mine, 'company')
        ->get(route('portal.company.edit'))
        ->assertInertia(fn ($page) => $page->where('company.id', $mine->company_id));

    expect(CompanyUser::query()->find($other->id)->company_id)->not->toBe($mine->company_id);
});
