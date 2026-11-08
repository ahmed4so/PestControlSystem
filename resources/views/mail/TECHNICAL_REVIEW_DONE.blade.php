{{-- TECHNICAL_REVIEW_DONE (07 §3.3), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('The technical review of application :reference of :company is completed.', ['reference' => $reference, 'company' => $company], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Open the application', [], $locale)])
