{{-- APPLICATION_RESUBMITTED (07 §3.3), plain text, one language. --}}
@php($stageLabel = $locale === 'so' ? $stage_so : $stage_en)
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __(':company resubmitted application :reference (version :version). It is waiting for :stage.', ['company' => $company, 'reference' => $reference, 'version' => $version, 'stage' => __($stageLabel, [], $locale)], $locale) }}

{{ __('Open the application', [], $locale) }}: {{ $url }}
