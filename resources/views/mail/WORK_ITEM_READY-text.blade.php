{{-- WORK_ITEM_READY (07 §3.3), plain text, one language. --}}
@php($serviceLabel = __($service, [], $locale).($category !== '' ? ' — '.__($category, [], $locale) : ''))
@php($stageLabel = $locale === 'so' ? $stage_so : $stage_en)
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('Application :reference of :company is waiting for :stage.', ['reference' => $reference, 'company' => $company, 'stage' => __($stageLabel, [], $locale)], $locale) }}

{{ $serviceLabel }}

{{ __('Open the application', [], $locale) }}: {{ $url }}
