<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Traits\DateMutateFieldsTrait;
use App\Utils\Filament\Actions\QuickActionsHelper;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class AppointmentModalForm extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use DateMutateFieldsTrait;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema(QuickActionsHelper::appointmentModalForm())->statePath('data');
    }

    public function create(): void
    {
        $formData = $this->form->getState();

        $data = $this->combineDateFields($formData);

        Appointment::create($data);

        $this->dispatch('closeModal');
    }

    public function render()
    {
        return view('livewire.appointment-modal-form');
    }
}
