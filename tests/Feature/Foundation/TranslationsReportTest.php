<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

// D-154: report Somali entries still identical to the English.
beforeEach(function () {
    $this->langPath = storage_path('framework/testing/lang-'.uniqid());
    File::ensureDirectoryExists($this->langPath);
    $this->app->useLangPath($this->langPath);
});

afterEach(function () {
    File::deleteDirectory($this->langPath);
});

function writeLang(string $path, array $en, array $so): void
{
    File::put("{$path}/en.json", json_encode($en));
    File::put("{$path}/so.json", json_encode($so));
}

it('lists Somali entries that still equal the English text', function () {
    writeLang($this->langPath, ['Users' => 'Users', 'Roles' => 'Roles'], ['Users' => 'Isticmaalayaasha', 'Roles' => 'Roles']);

    $this->artisan('translations:report')
        ->expectsOutputToContain('Roles')
        ->expectsOutputToContain('1 of 2 Somali entries still equal the English text.')
        ->assertSuccessful();
});

it('fails with --fail while any entry is untranslated', function () {
    writeLang($this->langPath, ['Users' => 'Users'], ['Users' => 'Users']);

    $this->artisan('translations:report --fail')->assertFailed();
});

it('passes with --fail when every entry is translated', function () {
    writeLang($this->langPath, ['Users' => 'Users'], ['Users' => 'Isticmaalayaasha']);

    $this->artisan('translations:report --fail')
        ->expectsOutputToContain('0 of 1 Somali entries')
        ->assertSuccessful();
});
