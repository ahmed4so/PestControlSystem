{{-- APPLICATION_ACKNOWLEDGED (07 §3.3), one language. --}}
@php($serviceLabel = __($service, [], $locale).($category !== '' ? ' — '.__($category, [], $locale) : ''))
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('Your :service application :reference is now under review.', ['service' => $serviceLabel, 'reference' => $reference], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Open your application', [], $locale)])
