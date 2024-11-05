<?php

namespace App\Livewire\FinancialReport;

use App\Enums\PrimaryColor;
use App\Models\Label;
use Livewire\Attributes\On;
use Livewire\Component;

class LabelProfitsChart extends Component
{
    public $labelProfits = [];

    public $year;

    public function mount()
    {
        $this->year = date('Y');
        $this->loadLabelProfits($this->year);
    }

    #[On('yearUpdated')]
    public function updateData($year)
    {
        $this->labelProfits = [];
        $this->year = $year;
        $this->loadLabelProfits($year);
    }

    protected function loadLabelProfits($year)
    {
        $labels = Label::where('type', 'opportunity')
            ->where('should_archive', 0)
            ->where('finished_state', 0)
            ->whereYear('created_at', $year)
            ->get();

        foreach ($labels as $label) {
            $this->labelProfits[] = [
                'label' => $label->name,
                'color' => PrimaryColor::tryFrom($label->color)?->getHexColor(),
                'profit' => $label->opportunities()
                    ->whereNotNull('expected_revenue')
                    ->whereYear('created_at', $year)
                    ->get()
                    ->sum(fn($opportunity) => $opportunity->expected_revenue - ($opportunity->cost_estimate ?? 0)),
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
        $this->loadLabelProfits($year);
    }

    public function render()
    {
        return view('livewire.label-profits-chart');
    }
}
