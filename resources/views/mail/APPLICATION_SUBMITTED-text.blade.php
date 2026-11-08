{{-- APPLICATION_SUBMITTED (07 §3.3), plain text, one language. --}}
@php($serviceLabel = __($service, [], $locale).($category !== '' ? ' — '.__($category, [], $locale) : ''))
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __(':service application :reference was received.', ['service' => $serviceLabel, 'reference' => $reference], $locale) }}

{{ __('SARIS will review it and email you at each step.', [], $locale) }}

{{ __('Open your application', [], $locale) }}: {{ $url }}
