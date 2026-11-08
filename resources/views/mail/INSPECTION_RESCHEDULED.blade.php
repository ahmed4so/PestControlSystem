{{-- INSPECTION_RESCHEDULED (07 §3.3), one language. --}}
<p style="margin:0 0 12px;">{{ __('Hello :name,', ['name' => $name], $locale) }}</p>
<p style="margin:0 0 12px;">{{ __('The inspection of :premises (application :reference) was moved to :date.', ['premises' => $premises, 'reference' => $reference, 'date' => $date], $locale) }}</p>
@include('mail.partials.button', ['url' => $url, 'label' => __('Open the application', [], $locale)])
