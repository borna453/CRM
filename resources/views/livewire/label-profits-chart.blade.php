<div class="space-y-6 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md filament-card flex">
    <div class="w-3/4">
        <livewire:financial-report.label-profits-chart-widget :wire:key="$year" :year="$year"/>
    </div>

    <div class="w-1/3 flex flex-col ml-4 justify-center space-y-4">
        @foreach($labelProfits as $labelProfit)
            <div class="flex items-center">
                <span class="w-4 h-4 rounded-full" style="background-color: {{ $labelProfit['color'] }}"></span>
                <span
                    class="ml-2 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $labelProfit['label'] }}</span>
            </div>
        @endforeach
    </div>
</div>
