@props(['status'])
@use('App\Utils\LabelColorHelper')
<div class="md:w-[24rem] flex-shrink-0 mb-5 md:min-h-full flex flex-col">
    @include(static::$headerView)

    <div
        @if(auth()->user()->can('createKanban', \App\Models\Opportunity::class))
        wire:click="openModalOnStatusClick({{ $status['id'] }})"
        @endif
        data-status-id="{{ $status['id'] }}"
        class="flex flex-col flex-1 gap-2 p-3 rounded-xl border-2"
        style="background-color: {{ LabelColorHelper::hexToRgba(LabelColorHelper::getLabelColorById($status['id']), 0.5) }};
           border-color: {{ LabelColorHelper::getLabelColorById($status['id']) }}"
    >
        @foreach($status['records'] as $record)
            @include(static::$recordView)
        @endforeach
    </div>
 </div>
