<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
@if($logo = \App\Utils\BrandLogoHelper::brandLogo())
<img src="{{ $logo }}" alt="{{ tenant()?->email['from_name'] ?? config('app.name') }}" style="max-height: 50px;" />
@else
{{ tenant()?->email['from_name'] ?? config('app.name') }}
@endif
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{{ $slot }}
@isset($raw)
<x-slot:raw>
{!! $raw !!}
</x-slot:raw>
@endisset
{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ tenant()?->email['from_name'] ?? config('app.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
