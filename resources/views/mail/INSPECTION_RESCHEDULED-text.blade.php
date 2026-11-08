{{-- INSPECTION_RESCHEDULED (07 §3.3), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('The inspection of :premises (application :reference) was moved to :date.', ['premises' => $premises, 'reference' => $reference, 'date' => $date], $locale) }}

{{ __('Open the application', [], $locale) }}: {{ $url }}
