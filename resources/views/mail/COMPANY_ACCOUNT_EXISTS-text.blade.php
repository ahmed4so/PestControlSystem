{{-- COMPANY_ACCOUNT_EXISTS (07 §3.1), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('Someone tried to sign up with this email, but you already have an account on the SARIS Company Portal. No new password was created.', [], $locale) }}

{{ __('If you forgot your password, use "Forgot password" on the login page.', [], $locale) }}


{{ __('Reset my password', [], $locale) }}: {{ $url }}
