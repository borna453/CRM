<div>
    @include('filament-panels::resources.pages.edit-record')

    <x-filament::modal id="suggest-tasks-modal" width="3xl">
        @if($tasks)
            <livewire:suggest-tasks-modal :tasks="$tasks" :model-id="$record->id" :model-type="\App\Models\Report::class"/>
        @endif
    </x-filament::modal>
</div>
