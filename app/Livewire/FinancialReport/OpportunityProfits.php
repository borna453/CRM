<?php

namespace App\Livewire\FinancialReport;

use App\Enums\PrimaryColor;
use App\Models\Label;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class OpportunityProfits extends Component
{
    public $labelProfits = [];
    public $totalProfit = 0;

    public $year;

    public function mount(): void
    {
        $this->year = date('Y');
        $this->loadData($this->year);
    }

    #[On('yearUpdated')]
    public function updateData($year): void
    {
        $this->labelProfits = [];
        $this->totalProfit = 0;
        $this->year = $year;
        $this->loadData($year);
    }

    public function loadData($year): void
    {
        $labels = Label::where('type', 'opportunity')
            ->where('finished_state', 0)
            ->where('should_archive', 0)
            ->whereYear('created_at', $year)
            ->get();

        foreach ($labels as $label) {
            $profit = $label->opportunities()
                ->whereNotNull('expected_revenue')
                ->whereYear('created_at', $year)
                ->get()
                ->sum(fn($opportunity) => $opportunity->expected_revenue - ($opportunity->cost_estimate ?? 0));

            $this->totalProfit += $profit;

            $this->labelProfits[] = [
                'label' => $label,
                'color' => PrimaryColor::tryFrom($label->color)?->getHexColor(),
                'profit' => $profit,
            ];
        }
    }

    public function hasData(): bool
    {
        return !empty($this->labelProfits);
    }

    public function setYear($year): void
    {
        $this->year = $year;
        $this->loadData($year);
    }

    public function render(): View
    {
        return view('livewire.opportunity-profits');
    }
}
