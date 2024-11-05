<?php

namespace App\Livewire;

use App\Models\Task;
use App\Utils\Filament\Actions\QuickActionsHelper;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class TaskModalForm extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $data = [
        'title' => null,
        'information' => null,
        'user_id' => null,
        'dt_complete_by' => null,
        'is_private' => null,
    ];

    public function form(Form $form): Form
    {
        return $form->schema(QuickActionsHelper::taskActionModalForm())->statePath('data');
    }

    public function create(): void
    {
        Task::create($this->form->getState());

        $this->dispatch('closeModal');
    }

    public function render()
    {
        return view('livewire.task-modal-form');
    }
}
