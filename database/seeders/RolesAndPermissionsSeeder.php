<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Enums\StaffRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the staff roles (D-023, D-051) and their permissions (03 §11.1).
 *
 * This list is authoritative: permissions not listed here are deleted, and the
 * old Super Admin role is removed. Workflow actions are authorised by the
 * workflow engine from the stage role (D-096); the permissions below cover the
 * capabilities outside the transition matrix.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    private const GUARD = 'web';

    /**
     * @return array<string, list<string>>
     */
    public static function permissionsByRole(): array
    {
        return [
            StaffRole::SystemAdministrator->value => [
                'view user',
                'create user',
                'update user',
                'deactivate user',
                'view role',
                'create role',
                'update role',
                'delete role',
                'assign role',
                'assign permissions',
                'manage reference data',
                'manage fee schedule',
                'manage restricted ingredients',
                'manage system settings',
                'handle account recovery',
                'release national id',
                'reset two factor',
                'unlock account',
                'view security audit log',
            ],
            StaffRole::PesticideRegistrar->value => [
                'view applications',
                'verify products',
                'request enforcement',
            ],
            StaffRole::TechnicalReviewer->value => [
                'view assigned applications',
            ],
            StaffRole::Inspector->value => [
                'view assigned applications',
            ],
            StaffRole::DepartmentalDirector->value => [
                'view applications',
                'request enforcement',
                'view audit log',
            ],
            StaffRole::PaymentOfficer->value => [
                'view applications',
            ],
            StaffRole::DirectorGeneral->value => [
                'view applications',
                'approve enforcement',
                'manage delegations',
                'view audit log',
            ],
        ];
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $byRole = self::permissionsByRole();
        $names = array_values(array_unique(array_merge(...array_values($byRole))));

        foreach ($names as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => self::GUARD]);
        }

        Permission::query()
            ->where('guard_name', self::GUARD)
            ->whereNotIn('name', $names)
            ->each(fn (Permission $permission) => $permission->delete());

        Role::query()->where('name', 'Super Admin')->each(fn (Role $role) => $role->delete());

        foreach ($byRole as $roleName => $permissions) {
            Role::query()
                ->firstOrCreate(['name' => $roleName, 'guard_name' => self::GUARD])
                ->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
