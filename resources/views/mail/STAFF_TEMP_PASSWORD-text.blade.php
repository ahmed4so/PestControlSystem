{{-- STAFF_TEMP_PASSWORD (07 §3.1), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('A SARIS staff account was created for you. Your temporary password is:', [], $locale) }}

    {{ $password }}

{{ __('It is valid for 24 hours. When you first log in you must choose a new password and set up two-factor authentication.', [], $locale) }}


{{ __('Log in to the Admin Portal', [], $locale) }}: {{ $url }}
