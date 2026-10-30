{{-- NATIONAL_ID_RELEASED (07 §3.2), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('SARIS removed :person from your company profile, because the person now works for another company.', ['person' => $person], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('Please update your profile and submit an amendment of your license.', [], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Open my company profile', [], $locale)])
