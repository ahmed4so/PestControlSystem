<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Passport Guard
    |--------------------------------------------------------------------------
    |
    | This guard must match the API guard that uses the `passport` driver so
    | password-grant user resolution uses the correct provider.
    |
    */

    'guard' => 'api',

    'middleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Password grant client (Flutter / mobile)
    |--------------------------------------------------------------------------
    |
    | Create with: php artisan passport:client --password --name="Mobile" --provider=users
    |
    | Use --public for a public client (no secret). Refresh tokens still use client_id.
    |
    */

    'password_client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),

    'password_client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    */

    'private_key' => env('PASSPORT_PRIVATE_KEY'),

    'public_key' => env('PASSPORT_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Passport Database Connection
    |--------------------------------------------------------------------------
    */

    'connection' => env('PASSPORT_CONNECTION'),

];
