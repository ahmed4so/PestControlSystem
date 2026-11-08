{{-- APPLICATION_ACKNOWLEDGED (07 §3.3), plain text, one language. --}}
@php($serviceLabel = __($service, [], $locale).($category !== '' ? ' — '.__($category, [], $locale) : ''))
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('Your :service application :reference is now under review.', ['service' => $serviceLabel, 'reference' => $reference], $locale) }}

{{ __('Open your application', [], $locale) }}: {{ $url }}
