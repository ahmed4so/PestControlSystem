<?php

declare(strict_types=1);

use App\Domain\Audit\Concerns\Audited;
use App\Domain\Audit\Support\Mask;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\StaffRole;
use App\Models\AuditActivity;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

// Phase 0 "Done when": an audit row cannot be updated or deleted (D-091, D-160).
it('refuses to update or delete audit entries, leaving the row unchanged', function () {
    $entry = activity('audit')->log('test entry');

    expect(fn () => $entry->update(['description' => 'changed']))->toThrow(LogicException::class)
        ->and(fn () => $entry->delete())->toThrow(LogicException::class);

    $row = DB::table('activity_log')->where('id', $entry->id)->first();

    expect($row)->not->toBeNull()
        ->and($row->description)->toBe('test entry');
});

it('has no database triggers on the audit log (D-160)', function () {
    $triggers = DB::table('information_schema.TRIGGERS')
        ->where('EVENT_OBJECT_SCHEMA', DB::connection()->getDatabaseName())
        ->where('EVENT_OBJECT_TABLE', 'activity_log')
        ->count();

    expect($triggers)->toBe(0);
});

it('logs staff user changes with before and after values, never the password', function () {
    $user = User::factory()->create();
    $user->update(['status' => AccountStatus::Inactive, 'password' => 'Another-Secret-9']);

    $entry = AuditActivity::query()->where('subject_id', $user->id)->where('event', 'updated')->latest('id')->firstOrFail();

    expect($entry->log_name)->toBe('audit')
        ->and($entry->properties['old']['status'])->toBe('ACTIVE')
        ->and($entry->properties['attributes']['status'])->toBe('INACTIVE')
        ->and($entry->properties->toJson())->not->toContain('password')
        ->and($entry->ip_address)->not->toBeNull();
});

it('records who made the change', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $staff = User::factory()->create();

    $entry = AuditActivity::query()->where('subject_id', $staff->id)->where('event', 'created')->firstOrFail();

    expect($entry->causer->is($admin))->toBeTrue();
});

it('masks sensitive attributes', function () {
    $model = new class extends Model
    {
        use Audited;

        protected $table = 'number_sequences';

        protected $fillable = ['prefix', 'year', 'last_number'];

        public function auditMaskedAttributes(): array
        {
            return ['prefix'];
        }
    };

    $model->newQuery()->create(['prefix' => 'SECRET1234', 'year' => 2026, 'last_number' => 0]);

    $entry = AuditActivity::query()->where('event', 'created')->latest('id')->firstOrFail();

    expect($entry->properties['attributes']['prefix'])->toBe('****1234')
        ->and(Mask::value('12345678901'))->toBe('****8901')
        ->and(Mask::value('123'))->toBe('****')
        ->and(Mask::value(null))->toBeNull();
});

it('logs role changes with the roles before and after', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = tap(User::factory()->create())->assignRole(StaffRole::SystemAdministrator->value);
    $staff = User::factory()->create();
    $registrar = Role::findByName(StaffRole::PesticideRegistrar->value);

    $this->actingAs($admin)->post(route('roles.assign.store'), ['user_id' => $staff->id, 'role_ids' => [$registrar->id]]);

    $entry = AuditActivity::query()->where('event', 'roles_changed')->firstOrFail();

    expect($entry->properties['old']['roles'])->toBe([])
        ->and($entry->properties['attributes']['roles'])->toBe([StaffRole::PesticideRegistrar->value])
        ->and($entry->causer->is($admin))->toBeTrue();
});
