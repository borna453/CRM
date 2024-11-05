<?php

namespace App\Livewire;

use App\Models\Enum\Features;
use App\Models\Enum\LocationOptions;
use App\Utils\Filament\Actions\QuickActionsHelper;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Component;

class TopbarActionDropdown extends Component implements HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function appointmentAction()
    {
        return QuickActionsHelper::appointmentAction()->getName();
    }

    public function taskAction()
    {
        return QuickActionsHelper::taskAction()->getName();
    }

    public function opportunityAction()
    {
        return QuickActionsHelper::opportunityAction()->getName();
    }

    public function companyAction()
    {
        return QuickActionsHelper::companyAction()->getName();
    }

    public function pinboardAction()
    {
        return QuickActionsHelper::pinboardAction()->getName();
    }

    public function render()
    {
        return view('livewire.topbar-action-dropdown');
    }
}
