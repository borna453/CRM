<?php

namespace App\Livewire;

use App\Models\Contract;
use Livewire\Attributes\On;
use Livewire\Component;

class ContractCostChart extends Component
{
    public $contractCostTotal = 0;
    public $year;

    public function mount(): void
    {
        $this->year = date('Y');
        $this->loadData($this->year);
    }

    #[On('yearUpdated')]
    public function updateData($year): void
    {
        $this->contractCostTotal = 0;
        $this->year = $year;
        $this->loadData($year);
    }

    public function loadData($year): void
    {
        $this->contractCostTotal = Contract::with(['costs' => function($query) use ($year) {
            $query->whereYear('created_at', $year);
        }])
            ->whereYear('created_at', $year)
            ->get()
            ->sum(function ($contract) {
                return $contract->costs->sum('cost_estimate') ?? 0;
            });
    }

    public function setYear($year): void
    {
        $this->year = $year;
        $this->loadData($year);
    }

    public function hasData(): bool
    {
        return $this->contractCostTotal > 0;
    }

    public function render()
    {
        return view('livewire.contract-cost-chart');
    }
}
