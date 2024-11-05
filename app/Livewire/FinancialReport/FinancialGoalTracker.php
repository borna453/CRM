<?php

namespace App\Livewire\FinancialReport;

use App\Models\Contract;
use App\Models\Deal;
use App\Models\FinancialGoal;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use Livewire\Component;

class FinancialGoalTracker extends Component implements HasForms
{
    use InteractsWithForms;

    public $goal = 0;
    public $achieved = 0;
    public $financialGoal;

    public $year;
    public ?array $data = [];

    public function mount()
    {
        $this->year = date('Y');
        $this->loadData($this->year);
    }

    #[On('yearUpdated')]
    public function updateData($year): void
    {
        $this->year = $year;
        $this->loadData($year);
    }

    public function loadData($year): void
    {
        $this->financialGoal = FinancialGoal::where('tenant_id', tenant()?->id)->where('year', $year)->first();

        $this->goal = $this->financialGoal?->goal;

        $contractAchieved = Contract::where('tenant_id', tenant()?->id)
            ->whereYear('created_at', $year)
            ->get()
            ->sum(function ($contract) {
                $totalContractCosts = $contract->costs()->whereNotNull('cost_estimate')->sum('cost_estimate');
                return $contract->value_per_month - $totalContractCosts;
            });

        $dealAchieved = Deal::where('tenant_id', tenant()?->id)
            ->whereYear('created_at', $year)
            ->sum('total_value');

        
        $this->achieved = $contractAchieved + $dealAchieved + ($this->financialGoal->achieved ?? 0);
    }

    public function setGoal($value)
    {
        $this->goal = $value;
    }

    public function updatedGoal($value)
    {
        if ($this->financialGoal) {
            $this->financialGoal->update(['goal' => $value]);
        } else {
            $this->financialGoal = FinancialGoal::create([
                'tenant_id' => tenant()?->id,
                'year' => date('Y'),
                'goal' => $value,
                'achieved' => $this->achieved,
            ]);
        }
    }

    public function getProgress()
    {
        return $this->goal > 0 ? ($this->achieved / $this->goal) * 100 : 0;
    }

    public function getProgressColor()
    {
        $progress = $this->getProgress();

        if ($progress < 50) {
            return '#f87171';
        } elseif ($progress < 100) {
            return '#fbbf24';
        } else {
            return '#4ade80';
        }
    }

    public function form(Form $form): Form
    {
        return $form->schema([
           Grid::make()->schema([
               TextInput::make('goal')
                   ->label(__('portal.financial_report.goal'))
                   ->numeric()
                   ->required()
                    ->columnSpan(4),
               TextInput::make('achieved')
                   ->label(__('portal.financial_report.achieved'))
                   ->columnSpan(4),
               Placeholder::make('year')
                   ->label(__('portal.year'))
                    ->content($this->year)
                    ->columnSpan(4),
           ])->columns(12)
        ])->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $data['tenant_id'] = tenant()?->id;
        $data['year'] = $this->year;
        FinancialGoal::create($data);

        $this->loadData($this->year);
    }

    public function render()
    {
        return view('livewire.financial-goal-tracker', [
            'progress' => $this->getProgress(),
            'progressColor' => $this->getProgressColor(),
        ]);
    }
}
