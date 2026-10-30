{{-- PASSWORD_RESET_LINK (07 §3.1), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('We received a request to reset your password. The link below is valid for 60 minutes and can be used once.', [], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('If you did not ask for this, you can ignore this email.', [], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Reset my password', [], $locale)])
