{{-- CERTIFICATE_UPDATED (07 §3.2), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __(':company uploaded a renewed :certificate.', ['company' => $company, 'certificate' => __($certificate, [], $locale)], $locale) }}

{{ __('Open the company file', [], $locale) }}: {{ $url }}
