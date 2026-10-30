<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Actions\CreateStaffUser;
use App\Domain\Identity\Actions\DeactivateStaffUser;
use App\Domain\Identity\Actions\IssueTemporaryPassword;
use App\Domain\Identity\Actions\ResetTwoFactor;
use App\Domain\Identity\Actions\UnlockAccount;
use App\Domain\Identity\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view user', only: ['index', 'show']),
            new Middleware('permission:create user', only: ['create', 'store', 'sendTemporaryPassword']),
            new Middleware('permission:update user', only: ['edit', 'update']),
            new Middleware('permission:suspend user', only: ['deactivate', 'activate']),
            new Middleware('permission:unlock account', only: ['unlock']),
            new Middleware('permission:reset two factor', only: ['resetTwoFactor']),
        ];
    }

    public function index(): Response
    {
        return Inertia::render('admin/users/index', [
            'users' => User::query()->with('roles:id,name')->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/users/create', [
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * The new user receives a temporary password by email (D-083).
     */
    public function store(StoreUserRequest $request, CreateStaffUser $createStaffUser): RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();

        /** @var array{name: string, email: string, phone?: string|null, preferred_locale: string} $data */
        $data = $request->safe()->only(['name', 'email', 'phone', 'preferred_locale']);

        $createStaffUser($data, $request->roleNames(), $admin);

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('User created. A temporary password was sent by email.'));
    }

    public function show(User $user): Response
    {
        $user->load('roles:id,name');

        return Inertia::render('admin/users/show', [
            'user' => [
                ...$user->only(['id', 'name', 'email', 'phone', 'status', 'preferred_locale', 'created_at', 'last_login_at']),
                'roles' => $user->roles->pluck('name'),
                'locked' => $user->isLocked(),
                'locked_until' => $user->locked_until?->toIso8601String(),
                'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
                'awaiting_first_login' => $user->first_login,
            ],
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin/users/edit', [
            'user' => $user->only(['id', 'name', 'email', 'phone', 'preferred_locale']),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('User updated.'));
    }

    /**
     * Staff users are never deleted (D-053).
     */
    public function deactivate(Request $request, User $user, DeactivateStaffUser $deactivate): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return back()->with('error', __('You cannot deactivate your own account.'));
        }

        $deactivate($user);

        return back()->with('success', __('User deactivated.'));
    }

    public function activate(User $user): RedirectResponse
    {
        $user->update(['status' => AccountStatus::Active]);

        return back()->with('success', __('User activated.'));
    }

    /**
     * Early unlock after a lockout (D-081).
     */
    public function unlock(Request $request, User $user, UnlockAccount $unlock): RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();

        $unlock($user, $admin);

        return back()->with('success', __('Account unlocked.'));
    }

    /**
     * 2FA reset after face-to-face verification (D-087).
     */
    public function resetTwoFactor(Request $request, User $user, ResetTwoFactor $resetTwoFactor): RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();

        if ($admin->is($user)) {
            return back()->with('error', __('You cannot reset your own two-factor authentication.'));
        }

        $resetTwoFactor($user, $admin);

        return back()->with('success', __('Two-factor authentication was reset. The user must set it up again at the next login.'));
    }

    /**
     * A new temporary password for a user who has not logged in yet (D-083).
     */
    public function sendTemporaryPassword(Request $request, User $user, IssueTemporaryPassword $issue): RedirectResponse
    {
        if (! $user->first_login) {
            return back()->with('error', __('This user has already set a password. They can use "Forgot password".'));
        }

        /** @var User $admin */
        $admin = $request->user();

        $issue($user, $admin);

        return back()->with('success', __('A new temporary password was sent by email.'));
    }
}
