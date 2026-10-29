<?php

declare(strict_types=1);

use App\Domain\Identity\Support\Translations;
use Illuminate\Support\Facades\File;

// D-054, D-116: UI text in lang/en.json and lang/so.json, English text as key.
function translationKeys(string $locale): array
{
    return array_keys(json_decode(File::get(lang_path("{$locale}.json")), true, flags: JSON_THROW_ON_ERROR));
}

it('has a Somali translation for every English key', function () {
    expect(array_diff(translationKeys('en'), translationKeys('so')))->toBe([]);
});

it('has no Somali key that is missing in English', function () {
    expect(array_diff(translationKeys('so'), translationKeys('en')))->toBe([]);
});

it('uses the English text as the key', function () {
    $lines = json_decode(File::get(lang_path('en.json')), true);

    foreach ($lines as $key => $value) {
        expect($value)->toBe($key);
    }
});

it('has an English key for every t() call in the React code', function () {
    $keys = translationKeys('en');
    $missing = [];

    foreach (File::allFiles(resource_path('js')) as $file) {
        if (! in_array($file->getExtension(), ['ts', 'tsx'], true)) {
            continue;
        }

        preg_match_all("/\\bt\\(\\s*'((?:[^'\\\\]|\\\\.)*)'/", $file->getContents(), $matches);

        foreach ($matches[1] as $key) {
            if (! in_array(stripslashes($key), $keys, true)) {
                $missing[] = "{$file->getRelativePathname()}: {$key}";
            }
        }
    }

    expect($missing)->toBe([]);
});

it('shares the current locale and its translations with every Inertia page', function () {
    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'en')
            ->where('translations', Translations::for('en'))
        );
});

it('falls back to the default locale for an unknown one', function () {
    expect(Translations::for('xx'))->toBe(Translations::for('en'));
});
