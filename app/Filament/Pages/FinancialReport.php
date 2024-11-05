<?php

namespace App\Filament\Pages;

use App\Livewire\ContractCostChart;
use App\Livewire\FinancialReport\ContractDealChart;
use App\Livewire\FinancialReport\LabelProfitsChart;
use App\Livewire\FinancialReport\OpportunityProfits;
use App\Models\FinancialGoal;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class FinancialReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-euro';

    protected static string $view = 'filament.pages.financial-report';

    public $year;

    public function mount(): void
    {
        $this->year = date('Y');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('last_year')
                ->label(fn() => date('Y', strtotime('-1 year')))
                ->disabled(fn() => $this->year === date('Y', strtotime('-1 year')))
                ->visible(fn() => FinancialGoal::where('year', date('Y', strtotime('-1 year')))->exists())
                ->action(fn() => $this->setYear(date('Y', strtotime('-1 year')))),
            Action::make('2024')
                ->label(date('Y'))
                ->disabled(fn() => $this->year === date('Y'))
                ->action(fn() => $this->setYear(date('Y'))),
            Action::make('2025')
                ->label(date('Y', strtotime('+1 year')))
                ->disabled(fn() => $this->year === date('Y', strtotime('+1 year')))
                ->action(fn() => $this->setYear(date('Y', strtotime('+1 year')))),
        ];
    }

    public function setYear($year): void
    {
        $this->year = $year;
        $this->dispatch('yearUpdated', $this->year);
    }

    public function opportunityProfitsHasData(): bool
    {
        $component = app(OpportunityProfits::class);
        $component->setYear($this->year);
        return $component->hasData();
    }

    public function contractDealChartHasData(): bool
    {
        $component = app(ContractDealChart::class);
        $component->setYear($this->year);
        return $component->hasData();
    }

    public function labelProfitsChartHasData(): bool
    {
        $component = app(LabelProfitsChart::class);
        $component->setYear($this->year);
        return $component->hasData();
    }

    public function contractChartHasData(): bool
    {
        $component = app(ContractCostChart::class);
        $component->setYear($this->year);
        return $component->hasData();
    }

    public function hasAnyData(): bool
    {
        return $this->opportunityProfitsHasData() || $this->contractDealChartHasData() || $this->labelProfitsChartHasData();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('portal.administration');
    }

    public function getTitle(): string|Htmlable
    {
        return __('portal.financial_report.financial_report');
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.financial_report.financial_report');
    }
}
