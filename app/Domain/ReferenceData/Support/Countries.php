<?php

declare(strict_types=1);

namespace App\Domain\ReferenceData\Support;

use ResourceBundle;

/**
 * ISO 3166-1 alpha-2 countries with names from the PHP intl data, in
 * English or Somali (05 §2 `country`).
 */
final class Countries
{
    /** Codes in the intl data that are not countries. */
    private const NOT_COUNTRIES = ['EU', 'EZ', 'UN', 'QO', 'ZZ', 'XA', 'XB'];

    /**
     * @return array<string, string> code => name, sorted by name
     */
    public static function all(string $locale = 'en'): array
    {
        $bundle = ResourceBundle::create($locale, 'ICUDATA-region') ?? ResourceBundle::create('en', 'ICUDATA-region');
        $countries = [];

        foreach ($bundle['Countries'] ?? [] as $code => $name) {
            if (is_string($code) && preg_match('/^[A-Z]{2}$/', $code) === 1 && ! in_array($code, self::NOT_COUNTRIES, true)) {
                $countries[$code] = (string) $name;
            }
        }

        asort($countries, SORT_LOCALE_STRING);

        return $countries;
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::all());
    }
}
