<?php

namespace App\Livewire\FinancialReport;

use App\Enums\PrimaryColor;
use App\Models\Label;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Livewire\Attributes\Computed;

class LabelProfitsChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'labelProfitsChartWidget';

    #[Computed]
    public $year;

    protected function getOptions(): array
    {
        $labels = Label::where('type', 'opportunity')
            ->whereYear('created_at', $this->year)
            ->where('should_archive', 0)
            ->where('finished_state', 0)
            ->get();

        $profits = [];
        $colors = [];
        $names = [];

        foreach ($labels as $label) {
            $profit = $label->opportunities()
                ->whereNotNull('expected_revenue')
                ->whereYear('created_at', $this->year)
                ->get()
                ->sum(fn($opportunity) => $opportunity->expected_revenue - $opportunity->cost_estimate);

            $colors[] = PrimaryColor::tryFrom($label->color)?->getHexColor();
            $profits[] = $profit;
            $names[] = $label->name;
        }

        return [
            'chart' => [
                'type' => 'pie',
                'height' => 350,
            ],
            'labels' => $names,
            'series' => $profits,
            'colors' => $colors,
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
