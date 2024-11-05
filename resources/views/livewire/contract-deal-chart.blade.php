<div class="space-y-6 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md filament-card flex flex-col md:flex-row">
    <div class="w-full md:w-1/2">
        <div class="-mt-2 mb-2 text-xl font-medium">
            {{ __('portal.financial_report.opportunity') }}
        </div>
        <livewire:financial-report.contract-deal-chart-widget :wire:key="$year" :year="$year"/>
    </div>

    <div class="w-full md:w-1/2 flex flex-col mt-6 md:mt-0 md:ml-4 justify-center space-y-4">
        <div class="flex justify-between items-center p-4"
             style="background-color: #008000; color: #FFFFFF; border-radius: 0.5rem;">
            <span class="text-lg font-medium">Contracts</span>
            <span class="text-lg font-semibold">
                €{{ number_format($contractTotal, 2) }}
            </span>
        </div>

        <div class="flex justify-between items-center p-4"
             style="background-color: #000080; color: #FFFFFF; border-radius: 0.5rem;">
            <span class="text-lg font-medium">Deals</span>
            <span class="text-lg font-semibold">
                €{{ number_format($dealTotal, 2) }}
            </span>
        </div>
    </div>
</div>
