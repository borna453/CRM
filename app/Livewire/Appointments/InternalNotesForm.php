<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Utils\RichEditorButtons;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class InternalNotesForm extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public ?array $data = [];

    public Appointment $appointment;
    public $internal_notes;

    public function mount(): void
    {
        $this->form->fill($this->appointment->toArray());
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make([
                RichEditor::make('internal_notes')
                    ->label(__('portal.appointments.internal_notes'))
                    ->default(fn ($livewire): string => $livewire->appointment->internal_notes ?? '')
                    ->toolbarButtons(RichEditorButtons::$toolbarButtons)
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state) {
                        $this->addNotes($state);
                    })
            ])
        ])->statePath('data')->model(Appointment::class);
    }

    public function addNotes($notes): void
    {
        $this->appointment->update(['internal_notes' => $notes]);
    }
    
    public function render()
    {
        return view('livewire.internal-notes-form');
    }
}
