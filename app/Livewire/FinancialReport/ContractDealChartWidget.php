<?php

namespace App\Livewire\FinancialReport;

use App\Models\Contract;
use App\Models\Deal;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class ContractDealChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'contractDealChartWidget';

    #[Computed]
    public $year;

    protected function getOptions(): array
    {
        $contractCostTotal = Contract::with(['costs' => function($query) {
            $query->whereYear('created_at', $this->year);
        }])
            ->whereYear('created_at', $this->year)
            ->get()
            ->sum(function ($contract) {
                return $contract->costs->sum('cost_estimate') ?? 0;
            });

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 350,
            ],
            'labels' => ['Contracts', 'Deals'],
            'series' => [
                Contract::whereYear('created_at', $this->year)->sum('value_per_month') - $contractCostTotal,
                Deal::whereYear('created_at', $this->year)->sum('total_value')
            ],
            'colors' => ['#008000', '#000080'],
            'legend' => [
                'show' => false,
            ],
            'responsive' => [
                [
                    'breakpoint' => 480,
                    'options' => [
                        'chart' => ['width' => 300],
                        'legend' => ['position' => 'bottom'],
                    ],
                ],
            ],
        ];
    }
}
