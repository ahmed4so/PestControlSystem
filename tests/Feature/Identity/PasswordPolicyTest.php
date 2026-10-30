<?php

declare(strict_types=1);

use App\Domain\Identity\Support\PasswordPolicy;
use App\Http\Requests\Admin\Settings\PasswordUpdateRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

// D-082: 12+ characters, upper and lower case, a number and a symbol.
function passesPolicy(string $password): bool
{
    return Validator::make(['password' => $password], ['password' => [PasswordPolicy::rule(checkBreached: false)]])->passes();
}

it('accepts a password that meets every rule', function () {
    expect(passesPolicy('Correct-Horse-7'))->toBeTrue();
});

it('rejects weak passwords', function (string $password) {
    expect(passesPolicy($password))->toBeFalse();
})->with([
    'too short' => 'Short-Pw-1',
    'no upper case' => 'lower-case-only-1',
    'no lower case' => 'UPPER-CASE-ONLY-1',
    'no number' => 'No-Number-Here!',
    'no symbol' => 'NoSymbolHere123',
]);

it('checks breached passwords outside tests', function () {
    expect(PasswordPolicy::rule()->appliedRules()['uncompromised'])->toBeTrue()
        ->and(PasswordPolicy::rule(checkBreached: false)->appliedRules()['uncompromised'])->toBeFalse();
});

it('never lets the System Administrator set a staff password (D-083)', function () {
    expect((new StoreUserRequest)->rules())->not->toHaveKey('password');
});

it('applies the policy to every new password, in both portals', function (string $requestClass, string $model) {
    $this->app->detectEnvironment(fn () => 'production');
    expect(app()->runningUnitTests())->toBeFalse();

    $request = (new $requestClass)->setUserResolver(fn () => $model::factory()->make());
    $rules = $request->rules()['password'];
    $passwordRule = collect($rules)->first(fn ($rule) => $rule instanceof Password);

    expect($passwordRule->appliedRules())->toMatchArray([
        'min' => PasswordPolicy::MIN_LENGTH,
        'mixedCase' => true,
        'numbers' => true,
        'symbols' => true,
        'uncompromised' => true,
    ]);
})->with([
    'staff' => [PasswordUpdateRequest::class, User::class],
    'company' => [App\Http\Requests\Portal\Settings\PasswordUpdateRequest::class, CompanyUser::class],
]);
