<?php

namespace App\Livewire\FinancialReport;

use App\Models\Contract;
use App\Models\Deal;
use Livewire\Attributes\On;
use Livewire\Component;

class ContractDealChart extends Component
{
    public $contractTotal = 0;
    public $dealTotal = 0;
    public $year;

    public function mount(): void
    {
        $this->year = date('Y');
        $this->loadData(date('Y'));
    }

    #[On('yearUpdated')]
    public function updateData($year): void
    {
        $this->contractTotal = 0;
        $this->dealTotal = 0;
        $this->year = $year;
        $this->loadData($year);
    }

    public function loadData($year): void
    {
        $contractCostTotal = Contract::with(['costs' => function($query) use ($year) {
            $query->whereYear('created_at', $year);
        }])
            ->whereYear('created_at', $year)
            ->get()
            ->sum(function ($contract) {
                return $contract->costs->sum('cost_estimate') ?? 0;
            });

        $this->contractTotal = Contract::whereYear('created_at', $year)
                ->sum('value_per_month') - $contractCostTotal;

        $this->dealTotal = Deal::whereYear('created_at', $year)
            ->sum('total_value');
    }

    public function hasData(): bool
    {
        return $this->contractTotal > 0 || $this->dealTotal > 0;
    }

    public function setYear($year): void
    {
        $this->year = $year;
        $this->loadData($year);
    }

    public function render()
    {
        return view('livewire.contract-deal-chart');
    }
}
