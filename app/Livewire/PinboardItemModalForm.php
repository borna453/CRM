<?php

namespace App\Livewire;

use App\Filament\User\Resources\PinboardItemsResource;
use App\Models\PinboardItem;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class PinboardItemModalForm extends Component implements HasForms, HasActions
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
        return $form->schema(PinboardItemsResource::getFormSchema())->statePath('data');
    }

    public function create()
    {
        PinboardItem::create($this->form->getState());

        $this->dispatch('closeModal');
    }

    public function render()
    {
        return view('livewire.pinboard-item-modal-form');
    }
}
