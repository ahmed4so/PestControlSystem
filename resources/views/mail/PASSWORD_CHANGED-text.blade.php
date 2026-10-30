{{-- PASSWORD_CHANGED (07 §3.1), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('Your password was changed, and your other sessions were signed out.', [], $locale) }}

{{ __('If you did not do this, contact SARIS straight away.', [], $locale) }}


{{ __('Log in', [], $locale) }}: {{ $url }}
