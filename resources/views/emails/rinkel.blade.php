@component('mail::message')
# {{ $name }}

{!! nl2br(e($callDetails)) !!}

@endcomponent
