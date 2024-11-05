<div>
    <div class="px-4" x-data="{ open: @entangle('openHistory') }">
        <x-slide-over-panel title="{{__('portal.appointments.history')}}" openVariable="open" widthClass="max-w-5xl">
            <x-slot name="content">
                <div class="relative flex-1 px-4 sm:px-6">
                    <livewire:appointments.history :user-id="$record->user->id" :current-report-id="$currentReportId"/>
                </div>
            </x-slot>
        </x-slide-over-panel>
    </div>

    @if($record->user->login_allowed && auth()->user()->can('viewAny', \App\Models\Task::class))
        <livewire:appointments.tasks-table :appointment="$record"/>
    @endif
    
    <x-filament::modal id="suggest-tasks-modal" width="3xl">
        @if($tasks)
            <livewire:suggest-tasks-modal :tasks="$tasks" :model-id="$record->id" :model-type="\App\Models\Appointment::class"/>
        @endif
    </x-filament::modal>
</div>
