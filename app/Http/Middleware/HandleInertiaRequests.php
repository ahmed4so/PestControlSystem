<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Enums\AuthArea;
use App\Domain\Identity\Support\Translations;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'csrf_token' => csrf_token(),
            // Only the current area's account is ever shared (D-048).
            'area' => AuthArea::current($request)?->value,
            'auth' => [
                'user' => fn () => $this->account($request),
                'permissions' => fn () => $request->user() instanceof User
                    ? $request->user()->getAllPermissions()->pluck('name')
                    : [],
            ],
            'locale' => app()->getLocale(),
            // UI text for t() in React (D-054).
            'translations' => fn () => Translations::for(app()->getLocale()),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * @return array{id: int, name: string, email: string, two_factor_enabled: bool}|null
     */
    private function account(Request $request): ?array
    {
        $user = $request->user();

        if (! $user instanceof User && ! $user instanceof CompanyUser) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->displayName(),
            'email' => $user->email,
            'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
        ];
    }
}
