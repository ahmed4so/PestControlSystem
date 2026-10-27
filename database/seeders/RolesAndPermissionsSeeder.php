<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        $permissions = [
            // ── User management ──────────────────────────────────────────────
            'view user',
            'create user',
            'update user',
            'delete user',
            'suspend user',

            // ── Role & permission management ─────────────────────────────────
            'view role',
            'create role',
            'update role',
            'delete role',
            'assign role',
            'assign permissions',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        Permission::query()
            ->where('guard_name', $guard)
            ->whereNotIn('name', $permissions)
            ->each(fn (Permission $p) => $p->delete());

        // Super Admin gets every ability through Gate::before in AppServiceProvider.
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guard])
            ->syncPermissions(Permission::query()->where('guard_name', $guard)->get());
    }
}
