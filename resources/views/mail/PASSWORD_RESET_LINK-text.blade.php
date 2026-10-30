{{-- PASSWORD_RESET_LINK (07 §3.1), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('We received a request to reset your password. The link below is valid for 60 minutes and can be used once.', [], $locale) }}

{{ __('If you did not ask for this, you can ignore this email.', [], $locale) }}


{{ __('Reset my password', [], $locale) }}: {{ $url }}
