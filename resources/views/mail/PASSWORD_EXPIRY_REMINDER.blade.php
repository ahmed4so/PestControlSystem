{{-- PASSWORD_EXPIRY_REMINDER (07 §3.1), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('Your password expires on :date. Please choose a new password before then.', ['date' => $date], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Change my password', [], $locale)])
