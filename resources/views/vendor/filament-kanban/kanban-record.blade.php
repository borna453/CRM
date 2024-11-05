<div
    id="{{ $record->getKey() }}"
    wire:click="recordClicked('{{ $record->getKey() }}', {{ @json_encode($record) }})"
    class="record bg-white dark:bg-gray-700 rounded-lg px-4 py-2 cursor-grab font-medium text-gray-600 dark:text-gray-200 flex flex-col justify-between break-words"
    @if($record->timestamps && now()->diffInSeconds($record->{$record::UPDATED_AT}) < 3)
        x-data
    x-init="
            $el.classList.add('animate-pulse-twice', 'bg-primary-100', 'dark:bg-primary-800')
            $el.classList.remove('bg-white', 'dark:bg-gray-700')
            setTimeout(() => {
                $el.classList.remove('bg-primary-100', 'dark:bg-primary-800')
                $el.classList.add('bg-white', 'dark:bg-gray-700')
            }, 3000)
        "
    @endif
    @click.stop
>
    <div class="mb-2 font-bold">
        {{ $record->{static::$recordTitleAttribute} }}
    </div>
    <div class="flex flex-col">
        <div class="flex-1">
            {!! $record->text !!}
        </div>
        @if(($record->expected_revenue > 0) || !empty($record->expected_revenue))
        <div class="text-right text-lg font-semibold mt-2">
            {{ $record->formatted_revenue }}
        </div>
        @endif
        @if(!empty($record->cost_estimate))
            <div class="text-right text-sm italic text-gray-500 mt-1">
                {{ $record->formatted_cost_estimate }}
            </div>
        @endif
    </div>
</div>
