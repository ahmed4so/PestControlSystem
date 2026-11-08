{{-- REVIEWER_ASSIGNED (07 §3.3), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('You are assigned to the technical review of application :reference of :company.', ['reference' => $reference, 'company' => $company], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Open the application', [], $locale)])
