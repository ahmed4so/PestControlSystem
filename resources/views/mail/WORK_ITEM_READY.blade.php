{{-- WORK_ITEM_READY (07 §3.3), one language. --}}
@php($serviceLabel = __($service, [], $locale).($category !== '' ? ' — '.__($category, [], $locale) : ''))
@php($stageLabel = $locale === 'so' ? $stage_so : $stage_en)
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('Application :reference of :company is waiting for :stage.', ['reference' => $reference, 'company' => $company, 'stage' => __($stageLabel, [], $locale)], $locale) }}</p>
<p style="margin:0 0 12px;">{{ $serviceLabel }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Open the application', [], $locale)])
