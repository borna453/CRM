<x-mail::message>
This is a test email.

@if($footer = tenant()?->email['footer'] ?? null)
{!! $footer !!}
@else
@lang('portal.notifications.regards'),<br>
{{ tenant()?->email['from_name'] ?? ucwords(tenant()?->id) }}
@endif
</x-mail::message>
