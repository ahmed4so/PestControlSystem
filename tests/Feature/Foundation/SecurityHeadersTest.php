<?php

declare(strict_types=1);

// D-090: nonce-based CSP, frame denial, nosniff, referrer and permissions policies.
it('sends the security headers on every page', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy');
});

it('sends a nonce-based Content-Security-Policy that the page scripts use', function () {
    $response = $this->get(route('home'));

    $csp = (string) $response->headers->get('Content-Security-Policy');
    preg_match("/script-src 'self' 'nonce-([^']+)'/", $csp, $matches);

    expect($matches)->not->toBeEmpty()
        ->and($csp)->toContain("frame-ancestors 'none'")
        ->and($csp)->toContain("object-src 'none'")
        ->and($csp)->not->toContain("script-src 'self' 'unsafe-inline'");

    $html = $response->getContent();
    preg_match_all('/<script\b[^>]*>/', $html, $scripts);

    expect($scripts[0])->not->toBeEmpty();
    foreach ($scripts[0] as $tag) {
        expect($tag)->toContain('nonce="'.$matches[1].'"');
    }
});

it('uses a new nonce for every request', function () {
    $first = $this->get(route('home'))->headers->get('Content-Security-Policy');
    $this->refreshApplication();
    $second = $this->get(route('home'))->headers->get('Content-Security-Policy');

    expect($first)->not->toBe($second);
});
