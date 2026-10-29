<?php

declare(strict_types=1);

namespace App\Domain\Identity\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * UI text lives in lang/{locale}.json with the English text as the key
 * (D-054, D-116). This loads one locale's file for sharing with React.
 */
final class Translations
{
    public const LOCALES = ['en', 'so'];

    /**
     * @return array<string, string>
     */
    public static function for(string $locale): array
    {
        if (! in_array($locale, self::LOCALES, true)) {
            $locale = (string) config('app.fallback_locale');
        }

        $path = lang_path("{$locale}.json");

        return Cache::rememberForever("translations.{$locale}.".File::lastModified($path), function () use ($path): array {
            /** @var array<string, string> $lines */
            $lines = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);

            return $lines;
        });
    }
}
