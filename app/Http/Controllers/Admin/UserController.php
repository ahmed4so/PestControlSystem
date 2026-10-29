<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Actions\DeactivateStaffUser;
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

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view user', only: ['index', 'show']),
            new Middleware('permission:create user', only: ['create', 'store']),
            new Middleware('permission:update user', only: ['edit', 'update']),
            new Middleware('permission:suspend user', only: ['deactivate', 'activate']),
        ];
    }

    /**
     * Display a listing of the users.
     */
    public function index(): Response
    {
        $users = User::query()
            ->latest()
            ->get();

        return Inertia::render('users/index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        return Inertia::render('users/create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create([
            ...$request->validated(),
            'status' => AccountStatus::Active,
            'first_login' => true,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        $user->load('roles');

        return Inertia::render('users/show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('users/edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Deactivate a staff user. Staff users are never deleted (D-053).
     */
    public function deactivate(Request $request, User $user, DeactivateStaffUser $deactivate): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $deactivate($user);

        return back()->with('success', 'User deactivated.');
    }

    /**
     * Reactivate a deactivated staff user.
     */
    public function activate(User $user): RedirectResponse
    {
        $user->update(['status' => AccountStatus::Active]);

        return back()->with('success', 'User activated.');
    }
}
