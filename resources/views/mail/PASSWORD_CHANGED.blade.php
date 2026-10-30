{{-- PASSWORD_CHANGED (07 §3.1), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('Your password was changed, and your other sessions were signed out.', [], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('If you did not do this, contact SARIS straight away.', [], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Log in', [], $locale)])
