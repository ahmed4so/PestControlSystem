<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

/**
 * The two identity realms (03-auth-security §2, D-048). Each area has its
 * own guard, password broker, session cookie and table, timeouts and route
 * names; SetAuthArea applies them per request (D-079).
 */
enum AuthArea: string
{
    case Admin = 'admin';
    case Company = 'company';

    public const REQUEST_ATTRIBUTE = 'auth_area';

    public static function current(?Request $request = null): ?self
    {
        $request ??= request();
        $area = $request->attributes->get(self::REQUEST_ATTRIBUTE);

        return $area instanceof self ? $area : null;
    }

    /**
     * The area named by a route's `area:` middleware. Laravel builds the
     * controller before route middleware runs, so code that resolves a
     * guard at that moment reads it from the route (D-079).
     */
    public static function forRoute(?Route $route): ?self
    {
        foreach ((array) $route?->middleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, 'area:')) {
                return self::tryFrom(substr($middleware, 5));
            }
        }

        return null;
    }

    public static function forAccount(User|CompanyUser $account): self
    {
        return $account instanceof User ? self::Admin : self::Company;
    }

    public function guard(): string
    {
        return $this->setting('guard');
    }

    public function broker(): string
    {
        return $this->setting('broker');
    }

    public function home(): string
    {
        return $this->setting('home');
    }

    public function sessionCookie(): string
    {
        return $this->setting('session_cookie');
    }

    public function sessionTable(): string
    {
        return $this->setting('session_table');
    }

    public function sessionPath(): string
    {
        return $this->setting('session_path');
    }

    public function idleMinutes(): int
    {
        return (int) $this->setting('idle_minutes');
    }

    public function absoluteHours(): ?int
    {
        $hours = config("identity.areas.{$this->value}.absolute_hours");

        return $hours === null ? null : (int) $hours;
    }

    public function passwordConfirmSeconds(): int
    {
        return (int) $this->setting('password_confirm_seconds');
    }

    /**
     * Prefix of this area's route names: `admin.login`, `portal.login`.
     */
    public function routePrefix(): string
    {
        return $this === self::Admin ? 'admin.' : 'portal.';
    }

    public function route(string $name): string
    {
        return $this->routePrefix().$name;
    }

    /**
     * Inertia page folder for this area (D-061).
     */
    public function pages(): string
    {
        return $this === self::Admin ? 'admin' : 'portal';
    }

    /**
     * @return class-string<User|CompanyUser>
     */
    public function model(): string
    {
        return $this === self::Admin ? User::class : CompanyUser::class;
    }

    private function setting(string $key): string
    {
        return (string) config("identity.areas.{$this->value}.{$key}");
    }
}
