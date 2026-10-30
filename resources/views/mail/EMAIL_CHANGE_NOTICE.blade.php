{{-- EMAIL_CHANGE_NOTICE (07 §3.1), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('A change of the login email of your SARIS Company Portal account to :new_email was requested.', ['new_email' => $new_email], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('If this was not you, contact SARIS straight away.', [], $locale) }}</p>
