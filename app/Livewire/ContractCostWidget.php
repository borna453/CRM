<?php

namespace App\Livewire;

use App\Models\Contract;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Livewire\Attributes\Computed;

class ContractCostWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'contractCostChartWidget';

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
            'labels' => ['Contract Costs'],
            'series' => [
                $contractCostTotal
            ],
            'colors' => ['#008000'],
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
