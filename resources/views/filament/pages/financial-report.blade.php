<x-filament-panels::page>
    <div class="mb-6">
        <livewire:financial-report.financial-goal-tracker />
    </div>

    @if($this->hasAnyData())
        <div class="flex flex-col md:flex-row space-y-6 md:space-x-6 mb-6">
            @if($this->opportunityProfitsHasData())
                <livewire:financial-report.opportunity-profits :year="$year" />
            @endif

            @if($this->contractDealChartHasData())
                <div class="flex-grow">
                    <livewire:financial-report.contract-deal-chart :year="$year" />
                </div>
            @endif
        </div>

        @if($this->contractChartHasData())
            <div>
                <livewire:contract-cost-chart :year="$year" />
            </div>
        @endif

        @if($this->labelProfitsChartHasData())
            <div>
                <livewire:financial-report.label-profits-chart :year="$year" />
            </div>
        @endif
    @else
        <div class="flex justify-center items-center mt-10">
            <div class="flex justify-center items-center mt-10">
                <div class="p-4 rounded-md shadow-md bg-gray-50 dark:bg-gray-800">
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ __('portal.financial_report.no_data') }}</p>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
