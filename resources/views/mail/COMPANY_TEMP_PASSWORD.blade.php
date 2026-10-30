{{-- COMPANY_TEMP_PASSWORD (07 §3.1), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('Your account on the SARIS Company Portal is ready. Your temporary password is:', [], $locale) }}</p>
<p style="margin:12px 0;padding:12px;background:#f4f5f2;border:1px solid #dfe3da;font-family:monospace;font-size:20px;letter-spacing:2px;text-align:center;">{{ $password }}</p>
<p style="margin:0 0 12px;">{{ __('It is valid for 1 hour. You must choose a new password when you first log in.', [], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Log in', [], $locale)])
