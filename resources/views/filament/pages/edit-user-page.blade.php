<div>
    @include('components.custom-edit-record')
    <div class="px-4" x-data="{ open: @entangle('openCustomerOverview') }">
        <x-slide-over-panel title="{{__('portal.users.customer_overview')}}" openVariable="open" widthClass="max-w-5xl">
            <x-slot name="content">
                <div class="relative flex-1 px-4 sm:px-6">
                    <livewire:customer-overview :user-id="$record->id"/>
                </div>
            </x-slot>
        </x-slide-over-panel>
    </div>
</div>
