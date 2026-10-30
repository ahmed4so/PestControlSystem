{{-- CERTIFICATE_UPDATED (07 §3.2), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __(':company uploaded a renewed :certificate.', ['company' => $company, 'certificate' => __($certificate, [], $locale)], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Open the company file', [], $locale)])
