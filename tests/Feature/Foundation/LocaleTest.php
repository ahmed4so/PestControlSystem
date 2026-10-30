<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/*
| Language (D-032, D-054, D-169) and self-hosted fonts (D-157).
*/

it('lets a guest switch to Somali with a cookie', function () {
    $this->from(route('portal.login'))
        ->post(route('locale.update', 'so'))
        ->assertRedirect(route('portal.login'))
        ->assertCookie('locale', 'so');

    $this->withCookie('locale', 'so')
        ->get(route('portal.login'))
        ->assertInertia(fn ($page) => $page->where('locale', 'so'));
});

it('refuses an unknown language', function () {
    $this->post('/locale/fr')->assertNotFound();
});

it('uses the logged-in account language and saves a switch', function () {
    $company = companyUser(['preferred_locale' => 'so']);

    $this->actingAs($company, 'company')
        ->get(route('portal.dashboard'))
        ->assertInertia(fn ($page) => $page->where('locale', 'so'));

    $this->actingAs($company, 'company')->post(route('locale.update', 'en'));

    expect($company->fresh()->preferred_locale)->toBe('en');
});

it('has an English key for every __() call in PHP and Blade', function () {
    $keys = array_keys(json_decode(File::get(lang_path('en.json')), true));
    $missing = [];

    foreach ([app_path(), resource_path('views')] as $directory) {
        foreach (File::allFiles($directory) as $file) {
            preg_match_all("/__\\(\\s*'((?:[^'\\\\]|\\\\.)*)'/", $file->getContents(), $matches);

            foreach ($matches[1] as $key) {
                $key = stripslashes($key);

                if (! in_array($key, $keys, true) && ! str_starts_with($key, 'auth.') && ! str_starts_with($key, 'The provided two factor')) {
                    $missing[] = "{$file->getRelativePathname()}: {$key}";
                }
            }
        }
    }

    expect($missing)->toBe([]);
});

it('allows fonts only from the portal itself', function () {
    $csp = (string) $this->get(route('home'))->headers->get('Content-Security-Policy');

    expect($csp)->toContain("font-src 'self'")
        ->and($csp)->not->toContain('bunny.net')
        ->and(File::get(resource_path('views/app.blade.php')))->not->toContain('fonts.bunny.net');
});

it('shows the landing page with links to both logins', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('public/home'));
});
