{{-- EMAIL_CHANGE_NOTICE (07 §3.1), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('A change of the login email of your SARIS Company Portal account to :new_email was requested.', ['new_email' => $new_email], $locale) }}

{{ __('If this was not you, contact SARIS straight away.', [], $locale) }}
