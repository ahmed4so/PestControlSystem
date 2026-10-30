{{-- ACCOUNT_LOCKED (07 §3.1), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('Your account was locked for 15 minutes after several failed sign-in attempts.', [], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('If this was not you, reset your password.', [], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Reset my password', [], $locale)])
