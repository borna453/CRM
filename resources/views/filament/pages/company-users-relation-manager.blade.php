<div>
    @include('filament-panels::resources.relation-manager')
    <div class="px-4" x-data="{ open: @entangle('openUserHistory') }">
        <x-slide-over-panel title="{{ __('portal.appointments.history') }}" openVariable="open" widthClass="max-w-5xl">
            <x-slot name="content">
                <div class="relative flex-1 px-4 sm:px-6">
                    @if ($selectedRecord)
                        <livewire:appointments.history :user-id="$selectedRecord->id" />
                    @endif
                </div>
            </x-slot>
        </x-slide-over-panel>
    </div>
</div>
