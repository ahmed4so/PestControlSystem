{{-- EMAIL_CHANGE_CONFIRM (07 §3.1), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('Please confirm that this is the new login email for your SARIS Company Portal account. The link is valid for 60 minutes.', [], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('Your login email changes only when you open the link.', [], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Confirm my new email', [], $locale)])
