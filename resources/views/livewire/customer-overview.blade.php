<div>
    @if($user->hasOpenPinboardItems())
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{__('portal.pinboard_items.pinboard')}}</h2>
        <livewire:pinboard-items-slideover :user-id="$userId"/>
    @endif

    @if($user->hasAppointments())
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{__('portal.appointments.history')}}</h2>
        <livewire:customer-appointments :user-id="$userId"/>
    @endif
</div>
