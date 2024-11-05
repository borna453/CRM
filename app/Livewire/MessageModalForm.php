<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Message;
use App\Models\User;
use App\Utils\Filament\FormFields\MessageHelper;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class MessageModalForm extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $data = [];

    public function mount()
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema(MessageHelper::formFields())->statePath('data');
    }

    public function create()
    {
        MessageHelper::create($this->form->getState());

        $this->dispatch('closeModal');
        $this->dispatch('refreshEngagementWidget');
        $this->dispatch('refreshListMessages');
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.message-modal-form');
    }
}
