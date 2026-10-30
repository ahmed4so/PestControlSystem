{{-- PASSWORD_EXPIRY_REMINDER (07 §3.1), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('Your password expires on :date. Please choose a new password before then.', ['date' => $date], $locale) }}


{{ __('Change my password', [], $locale) }}: {{ $url }}
