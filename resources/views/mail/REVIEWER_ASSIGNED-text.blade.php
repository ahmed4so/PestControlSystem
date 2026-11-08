{{-- REVIEWER_ASSIGNED (07 §3.3), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('You are assigned to the technical review of application :reference of :company.', ['reference' => $reference, 'company' => $company], $locale) }}

{{ __('Open the application', [], $locale) }}: {{ $url }}
