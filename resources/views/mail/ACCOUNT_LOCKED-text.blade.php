{{-- ACCOUNT_LOCKED (07 §3.1), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('Your account was locked for 15 minutes after several failed sign-in attempts.', [], $locale) }}

{{ __('If this was not you, reset your password.', [], $locale) }}


{{ __('Reset my password', [], $locale) }}: {{ $url }}
