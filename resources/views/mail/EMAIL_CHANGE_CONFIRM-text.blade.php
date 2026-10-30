{{-- EMAIL_CHANGE_CONFIRM (07 §3.1), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('Please confirm that this is the new login email for your SARIS Company Portal account. The link is valid for 60 minutes.', [], $locale) }}

{{ __('Your login email changes only when you open the link.', [], $locale) }}


{{ __('Confirm my new email', [], $locale) }}: {{ $url }}
