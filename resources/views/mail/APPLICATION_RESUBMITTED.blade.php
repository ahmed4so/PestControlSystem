{{-- APPLICATION_RESUBMITTED (07 §3.3), one language. --}}
@php($stageLabel = $locale === 'so' ? $stage_so : $stage_en)
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __(':company resubmitted application :reference (version :version). It is waiting for :stage.', ['company' => $company, 'reference' => $reference, 'version' => $version, 'stage' => __($stageLabel, [], $locale)], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Open the application', [], $locale)])
