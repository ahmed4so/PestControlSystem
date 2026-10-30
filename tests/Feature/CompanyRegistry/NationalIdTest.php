<?php

declare(strict_types=1);

use App\Domain\CompanyRegistry\Actions\SavePerson;
use App\Domain\CompanyRegistry\Support\NationalIdHasher;
use App\Domain\Notifications\Mail\NationalIdReleased;
use App\Models\AuditActivity;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Person;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

/*
| Persons and national IDs (02 §4.4, 05 §4.2, §6; D-066, D-088, D-095,
| D-117, D-181).
*/

beforeEach(fn () => Mail::fake());

function addPerson(CompanyUser $account, array $overrides = []): TestResponse
{
    return test()->actingAs($account, 'company')->post(route('portal.persons.store'), [
        'full_name' => 'Abdi Warsame',
        'national_id_no' => '12345678901',
        'nationality' => 'SO',
        ...$overrides,
    ]);
}

function companyOf(CompanyUser $account): Company
{
    return Company::query()->withoutGlobalScopes()->findOrFail($account->company_id);
}

it('stores the national ID encrypted with an HMAC hash for lookup', function () {
    $account = companyUser();

    addPerson($account, ['national_id_no' => '123 4567 8901'])->assertSessionHasNoErrors();

    $row = DB::table('persons')->sole();
    expect($row->national_id_no)->not->toContain('12345678901')
        ->and($row->national_id_hash)->toBe(hash_hmac('sha256', '12345678901', 'testing-only-national-id-hash-key'))
        ->and($row->active_nid_key)->toBe($row->national_id_hash)
        ->and(Person::query()->withoutGlobalScopes()->sole()->national_id_no)->toBe('12345678901');
});

it('requires exactly 11 digits', function (string $id) {
    addPerson(companyUser(), ['national_id_no' => $id])->assertSessionHasErrors('national_id_no');
})->with(['1234567890', '123456789012', 'ABCDEFGHIJK']);

it('blocks a national ID that is active in another company, without naming it', function () {
    $first = companyUser();
    $second = companyUser();
    addPerson($first)->assertSessionHasNoErrors();
    $firstName = companyOf($first)->name;

    addPerson($second)->assertSessionHasErrors(['national_id_no' => __(SavePerson::DUPLICATE_MESSAGE)]);

    expect(session('errors')->first('national_id_no'))->not->toContain($firstName)
        ->and(Person::query()->withoutGlobalScopes()->count())->toBe(1);
});

it('blocks the same national ID twice in one company too', function () {
    $account = companyUser();
    addPerson($account);

    addPerson($account, ['full_name' => 'Someone Else'])->assertSessionHasErrors('national_id_no');
});

it('keeps the database key even when the check is skipped (two saves at once)', function () {
    $hash = NationalIdHasher::hash('55555555555');
    Person::factory()->withNationalId('55555555555')->create();

    expect(fn () => Person::factory()->withNationalId('55555555555')->create())
        ->toThrow(UniqueConstraintViolationException::class);

    expect(Person::query()->withoutGlobalScopes()->where('national_id_hash', $hash)->count())->toBe(1);
});

it('lets a person keep their own national ID on update', function () {
    $account = companyUser();
    addPerson($account);
    $person = Person::query()->withoutGlobalScopes()->sole();

    $this->actingAs($account, 'company')
        ->put(route('portal.persons.update', $person), ['full_name' => 'Abdi W. Warsame', 'national_id_no' => '12345678901'])
        ->assertSessionHasNoErrors();
});

it('never puts the national ID in the audit log', function () {
    addPerson(companyUser());

    expect(AuditActivity::query()->get()->pluck('properties')->toJson())->not->toContain('12345678901');
});

it('lets the System Administrator release an ID; the old company is flagged and told, and the ID works again', function () {
    $old = companyUser();
    $new = companyUser();
    addPerson($old);
    $person = Person::query()->withoutGlobalScopes()->sole();
    companyOf($old)->forceFill(['owner_person_id' => $person->id])->save();
    $admin = systemAdministrator();

    $this->actingAs($admin, 'web')
        ->withSession(passwordConfirmed())
        ->post(route('admin.national-ids.release'), ['national_id_no' => '12345678901', 'reason' => 'Verified move with letter from new employer.'])
        ->assertSessionHas('success');

    $person->refresh();
    expect($person->released_at)->not->toBeNull()
        ->and($person->released_by)->toBe($admin->id)
        ->and(DB::table('persons')->where('id', $person->id)->value('active_nid_key'))->toBeNull()
        ->and(companyOf($old)->amendment_required_at)->not->toBeNull()
        ->and(AuditActivity::query()->where('event', 'national_id_released')->exists())->toBeTrue();
    Mail::assertQueued(NationalIdReleased::class, fn ($mail) => $mail->hasTo($old->email));

    addPerson($new)->assertSessionHasNoErrors();
    expect(Person::query()->withoutGlobalScopes()->whereNull('released_at')->sole()->company_id)->toBe($new->company_id);
});

it('makes a released person read-only for the old company', function () {
    $account = companyUser();
    $person = Person::factory()->create(['company_id' => $account->company_id]);
    $person->forceFill(['released_at' => now(), 'release_reason' => 'Moved to another company'])->save();

    $this->actingAs($account, 'company')
        ->put(route('portal.persons.update', $person), ['full_name' => 'Changed', 'national_id_no' => $person->national_id_no])
        ->assertForbidden();
});

it('requires the permission, a recent password confirmation and a reason to release', function () {
    $person = Person::factory()->withNationalId('99999999999')->create();

    $this->actingAs(staffUser(), 'web')
        ->withSession(passwordConfirmed())
        ->post(route('admin.national-ids.release'), ['national_id_no' => '99999999999', 'reason' => 'A long enough reason'])
        ->assertForbidden();

    $this->flushSession();
    $admin = systemAdministrator();
    $this->actingAs($admin, 'web')
        ->post(route('admin.national-ids.release'), ['national_id_no' => '99999999999', 'reason' => 'A long enough reason'])
        ->assertRedirect(route('admin.password.confirm'));

    expect($person->fresh()->released_at)->toBeNull();
});

it('requires a reason of at least 10 characters', function () {
    Person::factory()->withNationalId('99999999999')->create();

    $this->actingAs(systemAdministrator(), 'web')
        ->withSession(passwordConfirmed())
        ->post(route('admin.national-ids.release'), ['national_id_no' => '99999999999', 'reason' => 'short'])
        ->assertSessionHasErrors('reason');
});

it('finds the person by the full ID and shows it masked to the administrator', function () {
    Person::factory()->withNationalId('12312312312')->create(['full_name' => 'Hodan Ali']);

    $this->actingAs(systemAdministrator(), 'web')
        ->withSession(passwordConfirmed())
        ->post(route('admin.national-ids.find'), ['national_id_no' => '12312312312'])
        ->assertInertia(fn ($page) => $page
            ->where('match.full_name', 'Hodan Ali')
            ->where('match.national_id', '****2312'));
});
