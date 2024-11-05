<x-mail::message>
# Hallo {{ $user->name }},

Welkom op ons platform. We zijn blij dat je er bent.

**E-mail:** {{ $user->email }}

<x-mail::button :url="$resetUrl" color="primary">
{{ __('portal.notifications.user.reset_password') }}
</x-mail::button>

<x-mail::button :url="$loginUrl" color="success">
{{ __('portal.notifications.user.login') }}
</x-mail::button>

{{__('portal.notifications.user.regards')}},<br>
{{ tenant()?->email['from_name'] ?? config('app.name') }}
</x-mail::message>
