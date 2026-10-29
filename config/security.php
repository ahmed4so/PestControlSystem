<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Ignored audit advisories (D-153, D-161)
    |--------------------------------------------------------------------------
    |
    | Advisories that have no fix and are accepted for now. Each entry is
    | listed by advisory ID with the package and the reason, and must be
    | re-checked by its review_by date (monthly). `php artisan
    | security:npm-audit` fails once a date has passed. Remove an entry as
    | soon as a fix exists. The Composer entries are mirrored in
    | composer.json (config.audit.ignore), which Composer reads itself.
    |
    */

    'audit_allowlist' => [
        'composer' => [
            'PKSA-zk1n-qbm6-d3rq' => [
                'package' => 'web-auth/webauthn-lib',
                'severity' => 'low',
                'reason' => 'Pulled in by laravel/fortify through laravel/passkeys at a pinned version; passkeys are not used.',
                'review_by' => '2026-11-28',
            ],
        ],
        'npm' => [
            'GHSA-gcqx-w48m-w2h4' => [
                'package' => 'deepmerge',
                'severity' => 'low',
                'reason' => 'Pulled in by @heroui/alert through @heroui/theme (kept by D-111); no fixed version exists.',
                'review_by' => '2026-11-28',
            ],
        ],
    ],

];
