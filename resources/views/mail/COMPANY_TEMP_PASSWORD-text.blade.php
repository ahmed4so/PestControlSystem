{{-- COMPANY_TEMP_PASSWORD (07 §3.1), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('Your account on the SARIS Company Portal is ready. Your temporary password is:', [], $locale) }}

    {{ $password }}

{{ __('It is valid for 1 hour. You must choose a new password when you first log in.', [], $locale) }}


{{ __('Log in', [], $locale) }}: {{ $url }}
