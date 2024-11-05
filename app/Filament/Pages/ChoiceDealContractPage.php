<?php

namespace App\Filament\Pages;

use App\Enums\OpportunityChoice;
use App\Filament\Resources\ContractResource;
use App\Filament\Resources\DealResource;
use App\Models\Contract;
use App\Models\Deal;
use App\Models\Opportunity;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ChoiceDealContractPage extends Page
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.choice-deal-contract-page';

    protected static ?string $slug = 'choice-deal-contract/{recordId?}';

    public function getTitle(): string|Htmlable
    {
      return $this->customTitle();
    }

    public $choice;

    public ?array $data = [];
    public $company_id;
    public $total_value;
    public $value_per_month;
    public $selectedRecordId;

    public function mount($recordId = null)
    {
        $this->selectedRecordId = $recordId;
        $this->form->fill([
            'data' => $this->data,
        ]);
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    protected function getFormSchema(): array
    {
        return [
            Wizard::make([
                Wizard\Step::make(__('portal.companies.company'))
                    ->icon('heroicon-o-finger-print')
                    ->label(__('portal.opportunities.choice'))
                    ->schema([
                        Select::make('choice')
                            ->options(OpportunityChoice::options())
                            ->label(__('portal.opportunities.choice'))
                            ->reactive()
                            ->required()
                            ->afterStateUpdated(fn($state) => $this->choice = $state)
                    ]),
                Wizard\Step::make(__('portal.users.users'))
                    ->icon('heroicon-o-circle-stack')
                    ->label(__('portal.form'))
                    ->schema(function ($get, $set) {
                        $opportunity = Opportunity::find($this->selectedRecordId);
                        match($this->choice){
                            $set('company_id', $opportunity?->company_id),
                            OpportunityChoice::Deal->value => $set('total_value', $opportunity?->expected_revenue ?? $get('total_value')),
                            OpportunityChoice::Contract->value =>
                            [
                                $set('value_per_month', $opportunity?->expected_revenue ?? $get('value_per_month')),
                                $set('costs', [[
                                    'description' => $opportunity?->title ?? $get('description'),
                                    'cost_estimate' => $opportunity?->cost_estimate ?? $get('cost_estimate'),
                                ]]),
                            ],
                            default => null
                        };
                        return match ($this->choice) {
                            OpportunityChoice::Deal->value => DealResource::getFormSchema(),
                            OpportunityChoice::Contract->value => ContractResource::getFormSchema(),
                            default => []
                        };
                    })
                ->model(function (){
                    return match ($this->choice) {
                        OpportunityChoice::Deal->value => Deal::class,
                        OpportunityChoice::Contract->value => Contract::class,
                        default => null
                    };
                }),
            ])->columnSpanFull()
                ->submitAction(
                    Action::make('submit')
                        ->label(__('portal.create'))
                        ->action('submit')
                ),
        ];
    }

    public function submit()
    {
        $data = $this->form->getState();
        $costs = $data['costs'] ?? [];
        unset($data['costs'], $data['choice']);

        $data['tenant_id'] = tenant()?->id;

        if ($this->choice === OpportunityChoice::Deal->value) {
            unset($data['value_per_month']);
            Deal::create($data);

        } elseif ($this->choice === OpportunityChoice::Contract->value) {
            unset($data['total_value']);
            $contract = Contract::create($data);
        }

        if($costs){
            $contract?->costs()->createMany($costs);
        }

        $this->redirect(route('filament.admin.pages.opportunities-kanban-board'));
    }

    public function customTitle()
    {
        if ($this->choice)
        {
            if ($this->choice === OpportunityChoice::Deal->value ||    OpportunityChoice::Contract->value)
            {
                return $this->choice === OpportunityChoice::Deal->value ? 'Deal ' . __('portal.form') : 'Contract ' . __('portal.form');
            }
        }
        return __('portal.choice_form');
    }

}
