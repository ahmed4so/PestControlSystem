{{-- APPLICATION_RETURNED (07 §3.3), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('SARIS returned application :reference to you for correction.', ['reference' => $reference], $locale) }}

@foreach ($items as $item)
- {{ __($item['label'], [], $locale) }} ({{ __($item['result'], [], $locale) }}): {{ $item['comment'] }}
@endforeach
@if ($items !== [])

@endif
@if ($comment !== '')
{{ __('Comment from SARIS: :comment', ['comment' => $comment], $locale) }}

@endif
{{ __('Sign in, make the changes and resubmit the application.', [], $locale) }}

{{ __('Open your application', [], $locale) }}: {{ $url }}
