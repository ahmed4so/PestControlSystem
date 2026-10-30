<?php

declare(strict_types=1);

namespace App\Domain\CompanyRegistry\Actions;

use App\Domain\CompanyRegistry\Support\NationalIdHasher;
use App\Domain\CompanyRegistry\Support\ProfileCompleteness;
use App\Models\Company;
use App\Models\Person;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Creates or updates a person (02 §4.4). A national ID can be active in
 * one company only in the whole system (D-066); the duplicate message
 * never names the other company. The database key `active_nid_key` also
 * refuses two saves at the same moment.
 */
final class SavePerson
{
    public const DUPLICATE_MESSAGE = 'This national ID is already registered. Please contact SARIS.';

    /**
     * @param  array<string, mixed>  $data  validated fields, national_id_no as 11 digits
     */
    public function __invoke(Company $company, array $data, ?Person $person = null): Person
    {
        $person ??= new Person;
        $hash = NationalIdHasher::hash((string) $data['national_id_no']);

        $taken = Person::query()->withoutGlobalScopes()
            ->where('active_nid_key', $hash)
            ->when($person->exists, fn ($query) => $query->whereKeyNot($person->id))
            ->exists();

        if ($taken) {
            throw $this->duplicate();
        }

        try {
            return DB::transaction(function () use ($company, $data, $person, $hash): Person {
                $person->fill($data);
                $person->national_id_hash = $hash;
                $person->company()->associate($company);
                $person->save();

                ProfileCompleteness::refresh($company);

                return $person;
            });
        } catch (UniqueConstraintViolationException) {
            throw $this->duplicate();
        }
    }

    private function duplicate(): ValidationException
    {
        return ValidationException::withMessages(['national_id_no' => __(self::DUPLICATE_MESSAGE)]);
    }
}
