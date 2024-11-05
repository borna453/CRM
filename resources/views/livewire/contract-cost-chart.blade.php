<div class="space-y-6 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md filament-card">
    <div class="-mt-2 mb-2 text-xl font-medium">
        {{ __('portal.financial_report.contract_costs') }}
    </div>

    <livewire:contract-cost-widget :wire:key="$year" :year="$year"/>

    <div class="flex justify-between items-center p-4"
         style="background-color: #008000; color: #FFFFFF; border-radius: 0.5rem;">
        <span class="text-lg font-medium">Contract Costs</span>
        <span class="text-lg font-semibold">
            €{{ number_format($contractCostTotal, 2) }}
        </span>
    </div>
</div>
