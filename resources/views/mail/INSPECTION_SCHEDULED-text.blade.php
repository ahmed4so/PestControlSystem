{{-- INSPECTION_SCHEDULED (07 §3.3), plain text, one language. --}}
{{ __('Hello :name,', ['name' => $name], $locale) }}

{{ __('The inspection of :premises (application :reference) is planned for :date.', ['premises' => $premises, 'reference' => $reference, 'date' => $date], $locale) }}

{{ __('The Inspector will check the facilities, safety equipment and procedures on site.', [], $locale) }}

{{ __('Open the application', [], $locale) }}: {{ $url }}
