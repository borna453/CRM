{!! strip_tags($header ?? '') !!}

{!! strip_tags($slot) !!}

{!! strip_tags($raw ?? '') !!}

@isset($subcopy)

{!! strip_tags($subcopy) !!}
@endisset

{!! strip_tags($footer ?? '') !!}
