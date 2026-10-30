<?php

declare(strict_types=1);

/*
| Identity and account-security settings (03-auth-security). Durations are
| configuration, not code (08 §4.2 rule 12).
*/

return [

    // Per-area settings applied by SetAuthArea (D-048, D-079, D-084, D-085).
    'areas' => [
        'admin' => [
            'guard' => 'web',
            'broker' => 'users',
            'home' => '/admin',
            'session_cookie' => 'saris_admin_session',
            'session_table' => 'sessions',
            'session_path' => '/admin',
            'idle_minutes' => 15,
            'absolute_hours' => 12,
            'password_confirm_seconds' => 15 * 60,
        ],
        'company' => [
            'guard' => 'company',
            'broker' => 'company_users',
            'home' => '/portal',
            'session_cookie' => 'saris_portal_session',
            'session_table' => 'company_sessions',
            'session_path' => '/',
            'idle_minutes' => 30,
            'absolute_hours' => null,
            'password_confirm_seconds' => 30 * 60,
        ],
    ],

    // Temporary passwords (03 §4, §5.2; D-063, D-083).
    'temporary_password' => [
        'length' => 12,
        'company_minutes' => 60,
        'staff_hours' => 24,
    ],

    // Lockout (03 §7, D-081).
    'lockout' => [
        'max_attempts' => 5,
        'minutes' => 15,
    ],

    // Password expiry and reuse (03 §6, D-082).
    'password' => [
        'expires_after_days' => 90,
        'reminder_days_before' => 14,
        'history' => 5,
    ],

    // Login email change link (03 §5.1, D-093).
    'email_change_minutes' => 60,

    // Public form protection (03 §4, §14; D-080).
    'honeypot_seconds' => 3,

    // HMAC key for national-ID lookups and uniqueness (D-088, D-117).
    'nid_hash_key' => env('NID_HASH_KEY'),

    // Email footer until system_settings exists (07 §1, D-170).
    'contact' => [
        'email' => 'info@saris.gov.so',
        'phone' => '+252 612 221 431',
    ],

];
