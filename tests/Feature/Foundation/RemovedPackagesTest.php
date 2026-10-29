<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// D-049: Passport and Jetstream are removed with their routes.
it('registers no OAuth, API or Jetstream routes', function () {
    $uris = collect(Route::getRoutes()->getRoutes())->map(fn ($route) => $route->uri());

    expect($uris->filter(fn (string $uri) => str_starts_with($uri, 'oauth')))->toBeEmpty()
        ->and($uris->filter(fn (string $uri) => str_starts_with($uri, 'api')))->toBeEmpty()
        ->and($uris)->not->toContain('user/profile')
        ->and($uris)->not->toContain('user/other-browser-sessions')
        ->and($uris)->not->toContain('user/profile-photo')
        ->and($uris)->not->toContain('user');
});

it('has no Passport or Jetstream classes installed', function () {
    expect(class_exists('Laravel\Passport\Passport'))->toBeFalse()
        ->and(class_exists('Laravel\Jetstream\Jetstream'))->toBeFalse();
});
