{{-- TECHNICAL_REVIEW_DONE (07 §3.3), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('The technical review of application :reference of :company is completed.', ['reference' => $reference, 'company' => $company], $locale) }}

{{ __('Open the application', [], $locale) }}: {{ $url }}
