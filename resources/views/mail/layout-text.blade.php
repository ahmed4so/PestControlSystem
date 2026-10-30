SARIS

@foreach ($textSections as $section)
@unless ($loop->first)
------------------------------------------------------------

@endunless
{!! $section !!}

@endforeach
------------------------------------------------------------
@foreach ($textFooters as $footer)
{!! $footer !!}
@endforeach
