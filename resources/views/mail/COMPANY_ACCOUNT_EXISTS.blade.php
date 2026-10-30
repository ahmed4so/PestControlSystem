{{-- COMPANY_ACCOUNT_EXISTS (07 §3.1), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('Someone tried to sign up with this email, but you already have an account on the SARIS Company Portal. No new password was created.', [], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('If you forgot your password, use "Forgot password" on the login page.', [], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Reset my password', [], $locale)])
