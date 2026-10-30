<?php

declare(strict_types=1);

use App\Domain\Identity\Listeners\RecordSuccessfulLogin;
use App\Http\Middleware\EnforceSessionTimeouts;

/*
| Session timeouts (03 §9, D-084).
*/

it('logs staff out after 15 idle minutes', function () {
    $staff = staffUser();

    $this->actingAs($staff)
        ->withSession([EnforceSessionTimeouts::LAST_ACTIVITY => now()->subMinutes(16)->getTimestamp()])
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));

    $this->assertGuest('web');
});

it('keeps staff logged in within 15 idle minutes', function () {
    $this->actingAs(staffUser())
        ->withSession([EnforceSessionTimeouts::LAST_ACTIVITY => now()->subMinutes(14)->getTimestamp()])
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('keeps a company logged in for 30 idle minutes, not more', function () {
    $company = companyUser();

    $this->actingAs($company, 'company')
        ->withSession([EnforceSessionTimeouts::LAST_ACTIVITY => now()->subMinutes(25)->getTimestamp()])
        ->get(route('portal.dashboard'))
        ->assertOk();

    $this->actingAs($company, 'company')
        ->withSession([EnforceSessionTimeouts::LAST_ACTIVITY => now()->subMinutes(31)->getTimestamp()])
        ->get(route('portal.dashboard'))
        ->assertRedirect(route('portal.login'));
});

it('ends a staff session 12 hours after login, even when active', function () {
    $this->actingAs(staffUser())
        ->withSession([
            RecordSuccessfulLogin::SESSION_STARTED_AT => now()->subHours(12)->subMinute()->getTimestamp(),
            EnforceSessionTimeouts::LAST_ACTIVITY => now()->subMinute()->getTimestamp(),
        ])
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));
});

it('has no absolute limit for companies', function () {
    $this->actingAs(companyUser(), 'company')
        ->withSession([
            RecordSuccessfulLogin::SESSION_STARTED_AT => now()->subHours(20)->getTimestamp(),
            EnforceSessionTimeouts::LAST_ACTIVITY => now()->subMinute()->getTimestamp(),
        ])
        ->get(route('portal.dashboard'))
        ->assertOk();
});

it('sets the session lifetime per portal', function () {
    $this->get(route('admin.login'));
    expect(config('session.lifetime'))->toBe(15)
        ->and(config('auth.password_timeout'))->toBe(900);

    $this->get(route('portal.login'));
    expect(config('session.lifetime'))->toBe(30)
        ->and(config('auth.password_timeout'))->toBe(1800);
});
