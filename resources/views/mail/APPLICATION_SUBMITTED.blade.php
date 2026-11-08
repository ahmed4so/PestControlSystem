{{-- APPLICATION_SUBMITTED (07 §3.3), one language. --}}
@php($serviceLabel = __($service, [], $locale).($category !== '' ? ' — '.__($category, [], $locale) : ''))
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __(':service application :reference was received.', ['service' => $serviceLabel, 'reference' => $reference], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('SARIS will review it and email you at each step.', [], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Open your application', [], $locale)])
