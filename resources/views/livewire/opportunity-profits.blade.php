<div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md md:w-1/4 ">
    <div class="w-full md:w-1/4 text-xl font-medium -mt-2 mb-2">
        {{ __('portal.financial_report.opportunity') }}
    </div>

    <!-- Content Section -->
    <div class="w-full md:w-3/4 space-y-4">
        @foreach($labelProfits as $labelProfit)
            <div class="flex items-center space-x-2">
                <span class="w-4 h-4 rounded-full" style="background-color: {{ $labelProfit['color'] }}"></span>
                <span class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $labelProfit['label']->name }}</span>
                <span class="text-lg font-semibold text-green-600 dark:text-green-400 ml-auto">
                    €{{ number_format($labelProfit['profit'], 2) }}
                </span>
            </div>
        @endforeach

        <div class="flex justify-between items-center border-t pt-4 mt-4 border-gray-200 dark:border-gray-600">
            <span class="text-lg font-bold text-gray-900 dark:text-gray-100">Total:</span>
            <span class="text-lg font-bold text-green-600 dark:text-green-400">
                €{{ number_format($totalProfit, 2) }}
            </span>
        </div>
    </div>
</div>
