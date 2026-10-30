<?php

declare(strict_types=1);

use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

/*
| The two portals never share accounts, sessions or routes (D-048,
| 03-auth-security §3, §15 items 1 and 2).
*/

it('refuses a staff account on the company login', function () {
    $staff = staffUser();

    $this->post(route('portal.login.store'), ['email' => $staff->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest('company');
    $this->assertGuest('web');
});

it('refuses a company account on the staff login', function () {
    $company = companyUser();

    $this->post(route('admin.login.store'), ['email' => $company->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest('web');
    $this->assertGuest('company');
});

/**
 * Every GET route of an area that needs a login, with placeholder
 * parameters.
 *
 * @param  list<string>  $exceptNames
 * @return list<string>
 */
function areaGetUrls(string $namePrefix, array $exceptNames = []): array
{
    $urls = [];

    foreach (app('router')->getRoutes() as $route) {
        /** @var Route $route */
        $name = (string) $route->getName();
        $middleware = $route->gatherMiddleware();

        if (! str_starts_with($name, $namePrefix)
            || ! in_array('GET', $route->methods(), true)
            || in_array($name, $exceptNames, true)
            || ! array_intersect(['auth:web', 'auth:company'], $middleware)) {
            continue;
        }

        $urls[] = preg_replace('/\{[^}]+\}/', '1', '/'.ltrim($route->uri(), '/'));
    }

    return $urls;
}

it('keeps a company session out of every admin page', function () {
    $company = companyUser();
    $urls = areaGetUrls('admin.');

    expect(count($urls))->toBeGreaterThan(10);

    foreach ($urls as $url) {
        $this->actingAs($company, 'company')
            ->get($url)
            ->assertRedirect(route('admin.login'));
    }
});

it('keeps a staff session out of every company page', function () {
    $staff = staffUser();
    $urls = areaGetUrls('portal.');

    expect(count($urls))->toBeGreaterThan(5);

    foreach ($urls as $url) {
        $this->actingAs($staff, 'web')
            ->get($url)
            ->assertRedirect(route('portal.login'));
    }
});

it('does not accept a reset link of one portal in the other', function () {
    $staff = User::factory()->create(['email' => 'shared@example.com']);
    $company = CompanyUser::factory()->create(['email' => 'shared@example.com']);
    $staffToken = Password::broker('users')->createToken($staff);

    $this->post(route('portal.password.update'), [
        'token' => $staffToken,
        'email' => 'shared@example.com',
        'password' => 'Brand-New-Pass1!',
        'password_confirmation' => 'Brand-New-Pass1!',
    ])->assertSessionHasErrors('email');

    expect(password_verify('Brand-New-Pass1!', $company->fresh()->password))->toBeFalse();
});

it('uses a separate session cookie for each portal', function () {
    $this->get(route('admin.login'))->assertCookie('saris_admin_session');
    $this->get(route('portal.login'))->assertCookie('saris_portal_session');
});

it('scopes the staff session cookie to the admin URLs', function () {
    $cookie = collect($this->get(route('admin.login'))->headers->getCookies())
        ->first(fn ($cookie) => $cookie->getName() === 'saris_admin_session');

    expect($cookie->getPath())->toBe('/admin');
});

it('keeps the company logged in while staff log in on the same browser', function () {
    $staff = staffUser();
    $company = companyUser();

    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'password'])
        ->assertRedirect(route('portal.dashboard'));
    $this->post(route('admin.login.store'), ['email' => $staff->email, 'password' => 'password'])
        ->assertRedirect(route('admin.two-factor.login'));

    $this->get(route('portal.dashboard'))->assertOk();
    $this->assertAuthenticatedAs($company, 'company');
});

it('logs out of one portal without logging out of the other', function () {
    $staff = staffUser();
    $company = companyUser();

    $this->actingAs($staff, 'web');
    $this->actingAs($company, 'company');

    $this->post(route('portal.logout'))->assertRedirect(route('portal.login'));

    $this->assertGuest('company');
    $this->assertAuthenticatedAs($staff, 'web');
});

it('counts login attempts separately per portal', function () {
    $email = 'someone@example.com';

    foreach (range(1, 5) as $ignored) {
        $this->post(route('portal.login.store'), ['email' => $email, 'password' => 'wrong']);
    }

    $this->post(route('portal.login.store'), ['email' => $email, 'password' => 'wrong'])->assertTooManyRequests();
    $this->post(route('admin.login.store'), ['email' => $email, 'password' => 'wrong'])->assertSessionHasErrors('email');
});

it('shares only the current portal account with the page', function () {
    $staff = staffUser();
    $company = companyUser();

    $this->actingAs($staff, 'web');
    $this->actingAs($company, 'company')
        ->get(route('portal.dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('area', 'company')
            ->where('auth.user.email', $company->email)
            ->where('auth.permissions', []));
});

it('sets no remember-me cookie, even when asked', function () {
    $company = companyUser();

    $response = $this->post(route('portal.login.store'), [
        'email' => $company->email,
        'password' => 'password',
        'remember' => 'on',
    ]);

    foreach ($response->headers->getCookies() as $cookie) {
        expect($cookie->getName())->not->toStartWith('remember_');
    }
});

it('stores each portal session in its own table with the database driver', function () {
    $company = companyUser();

    // Start from a fresh session store, as each real request does.
    config(['session.driver' => 'database']);
    app('session')->forgetDrivers();
    app()->forgetInstance('session.store');
    app('auth')->forgetGuards();

    $this->post(route('portal.login.store'), ['email' => $company->email, 'password' => 'password'])
        ->assertRedirect(route('portal.dashboard'));

    expect(DB::table('company_sessions')->where('user_id', $company->id)->exists())->toBeTrue()
        ->and(DB::table('sessions')->where('user_id', $company->id)->exists())->toBeFalse();
});
