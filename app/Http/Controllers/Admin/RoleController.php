<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Enums\StaffRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignPermissionsRequest;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view role', only: ['index', 'permissions', 'assignRolesPage']),
            new Middleware('permission:create role', only: ['create', 'store']),
            new Middleware('permission:update role', only: ['edit', 'update']),
            new Middleware('permission:assign permissions', only: ['assignPermissions']),
            new Middleware('permission:assign role', only: ['assignRoles']),
            new Middleware('permission:delete role', only: ['destroy']),
        ];
    }

    public function index(): Response
    {
        $roles = Role::all();

        return Inertia::render('admin/roles/index', compact('roles'));
    }

    public function create(): Response
    {
        return Inertia::render('admin/roles/create');
    }

    public function store(StoreRoleRequest $request)
    {
        Role::create(
            [
                'guard_name' => 'web',
                ...$request->validated(),
            ]
        );

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully');
    }

    public function edit(Role $role): Response
    {
        return Inertia::render('admin/roles/edit', [
            'role' => $role,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role->update(
            [
                'guard_name' => 'web',
                ...$request->validated(),
            ]
        );

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role): RedirectResponse
    {
        // The designed staff roles cannot be deleted (D-023, D-051).
        if (StaffRole::tryFrom($role->name) !== null) {
            return redirect()->route('admin.roles.index')->with('error', 'This role is part of the approved design and cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully');
    }

    public function permissions(): Response
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy(function ($permission) {
            // Group by module (e.g., 'role', 'branch', 'invoice')
            $parts = explode(' ', $permission->name);

            return count($parts) > 1 ? end($parts) : 'other';
        });

        return Inertia::render('admin/roles/permissions', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function assignPermissions(AssignPermissionsRequest $request): RedirectResponse
    {
        $role = Role::findOrFail($request->validated('role_id'));
        $permissionIds = $request->validated('permission_ids', []);

        // Sync permissions (this will add new ones and remove those not in the list)
        $before = $role->permissions->pluck('name')->all();
        $role->syncPermissions($permissionIds);

        activity('audit')
            ->performedOn($role)
            ->event('permissions_changed')
            ->withProperties(['old' => ['permissions' => $before], 'attributes' => ['permissions' => $role->permissions()->pluck('name')->all()]])
            ->log('permissions_changed');

        return redirect()->route('admin.roles.permissions')->with('success', 'Permissions assigned successfully');
    }

    public function assignRolesPage(): Response
    {
        $users = User::with('roles')->get();
        $roles = Role::all();

        return Inertia::render('admin/roles/assign', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function assignRoles(AssignRoleRequest $request): RedirectResponse
    {
        $user = User::findOrFail($request->validated('user_id'));
        $roleIds = $request->validated('role_ids', []);

        // Sync roles (this will add new ones and remove those not in the list)
        $before = $user->getRoleNames()->all();
        $user->syncRoles($roleIds);

        // Role changes are audit-logged with before and after roles (03 §5.2).
        activity('audit')
            ->performedOn($user)
            ->event('roles_changed')
            ->withProperties(['old' => ['roles' => $before], 'attributes' => ['roles' => $user->getRoleNames()->all()]])
            ->log('roles_changed');

        return redirect()->route('admin.roles.assign')->with('success', 'Roles assigned successfully to '.$user->name);
    }
}
