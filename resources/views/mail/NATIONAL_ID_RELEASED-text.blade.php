{{-- NATIONAL_ID_RELEASED (07 §3.2), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('SARIS removed :person from your company profile, because the person now works for another company.', ['person' => $person], $locale) }}

{{ __('Please update your profile and submit an amendment of your license.', [], $locale) }}

{{ __('Open my company profile', [], $locale) }}: {{ $url }}
