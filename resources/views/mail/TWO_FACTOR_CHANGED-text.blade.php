{{-- TWO_FACTOR_CHANGED (07 §3.1), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}


@if ($change === 'enabled')
{{ __('Two-factor authentication was enabled on your account.', [], $locale) }}
@elseif ($change === 'disabled')
{{ __('Two-factor authentication was disabled on your account.', [], $locale) }}
@else
{{ __('Two-factor authentication on your account was reset by SARIS. You must set it up again at your next login.', [], $locale) }}
@endif


{{ __('If you did not do this, contact SARIS straight away.', [], $locale) }}


{{ __('Open my settings', [], $locale) }}: {{ $url }}
