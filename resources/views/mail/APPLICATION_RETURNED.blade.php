{{-- APPLICATION_RETURNED (07 §3.3), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('SARIS returned application :reference to you for correction.', ['reference' => $reference], $locale) }}</p>
@if ($items !== [])
<ul style="margin:0 0 12px;padding-left:20px;">
@foreach ($items as $item)
<li style="margin:0 0 6px;">{{ __($item['label'], [], $locale) }} ({{ __($item['result'], [], $locale) }}): {{ $item['comment'] }}</li>
@endforeach
</ul>
@endif
@if ($comment !== '')
<p style="margin:0 0 12px;">{{ __('Comment from SARIS: :comment', ['comment' => $comment], $locale) }}</p>
@endif
<p style="margin:0 0 12px;">{{ __('Sign in, make the changes and resubmit the application.', [], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Open your application', [], $locale)])
