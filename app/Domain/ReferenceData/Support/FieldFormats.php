<?php

declare(strict_types=1);

namespace App\Domain\ReferenceData\Support;

/**
 * Common field formats (05 §2, D-133) and normalisation before validation
 * (05 §1 rule 3, D-076). Form Requests use these so every form applies the
 * same rules.
 */
final class FieldFormats
{
    public const NAME_PATTERN = "/^[\\pL\\pM\\s'.\\-]+$/u";

    public const PHONE_PATTERN = '/^\+\d{8,15}$/';

    public const DEFAULT_COUNTRY_CODE = '+252';

    /**
     * @return list<string>
     */
    public static function name(): array
    {
        return ['string', 'min:2', 'max:150', 'regex:'.self::NAME_PATTERN];
    }

    /**
     * @return list<string>
     */
    public static function email(): array
    {
        return ['string', 'email:rfc', 'max:190'];
    }

    /**
     * @return list<string>
     */
    public static function phone(): array
    {
        return ['string', 'regex:'.self::PHONE_PATTERN];
    }

    public static function normaliseText(mixed $value): mixed
    {
        return is_string($value) ? trim(preg_replace('/\s+/u', ' ', $value) ?? $value) : $value;
    }

    public static function normaliseEmail(mixed $value): mixed
    {
        return is_string($value) ? mb_strtolower(trim($value)) : $value;
    }

    /**
     * International format; a number without a country code is Somali.
     */
    public static function normalisePhone(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $phone = preg_replace('/[\s\-().]/', '', trim($value)) ?? '';

        return match (true) {
            str_starts_with($phone, '+') => $phone,
            str_starts_with($phone, '00') => '+'.substr($phone, 2),
            str_starts_with($phone, '0') => self::DEFAULT_COUNTRY_CODE.substr($phone, 1),
            default => self::DEFAULT_COUNTRY_CODE.$phone,
        };
    }
}
