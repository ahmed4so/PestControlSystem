{{-- TWO_FACTOR_CHANGED (07 §3.1), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">
@if ($change === 'enabled')
{{ __('Two-factor authentication was enabled on your account.', [], $locale) }}
@elseif ($change === 'disabled')
{{ __('Two-factor authentication was disabled on your account.', [], $locale) }}
@else
{{ __('Two-factor authentication on your account was reset by SARIS. You must set it up again at your next login.', [], $locale) }}
@endif
</p>
<p style="margin:0 0 12px;">{{ __('If you did not do this, contact SARIS straight away.', [], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Open my settings', [], $locale)])
