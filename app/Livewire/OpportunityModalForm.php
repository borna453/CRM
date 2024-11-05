<?php

namespace App\Livewire;

use App\Filament\Resources\OpportunityResource;
use App\Models\Label;
use App\Models\Note;
use App\Models\Opportunity;
use App\Models\Task;
use App\Traits\ShowCompanySelectFieldTrait;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use Livewire\Component;

class OpportunityModalForm extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;
    use ShowCompanySelectFieldTrait;

    public ?array $data = [];

    public ?int $labelId = null;
    public ?int $companyId = null;

    public ?string $activeInstanceId = null;

    protected $listeners = [
        'open-modal' => 'handleOpenModal',
    ];

    public function mount()
    {
        $this->handleOpenModal($this->labelId, $this->companyId);
    }

    public function handleOpenModal($labelId = null, $companyId = null)
    {
        $this->labelId = $labelId;
        $this->companyId = $companyId;

        if ($this->initializedFieldsNullCheck()) {
            $this->form->fill([
                'company_id' => $companyId,
                'label_id' => $labelId ?? optional(Label::where('order_column', 1)->first())->id,
            ]);
        }
    }

    #[On('close-opportunity-modal')]
    public function emptyData(): void
    {
        $this->data = [];
    }

    public function form(Form $form): Form
    {
        return $form->schema(OpportunityResource::getFormSchema())->statePath('data')->model(Opportunity::class);
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $notesData = $data['notes'];
        unset($data['notes']);
        $taskData = $data['task'];
        unset($data['task']);

        $opportunity = Opportunity::create($data);

        if(!is_null($notesData['note'])){
            Note::create([
                'note' => $notesData['note'],
                'model_id' => $opportunity->id,
                'model_type' => $opportunity->getMorphClass(),
            ]);
        }

        if(!is_null($taskData['title'])) {
            $task = Task::create([
                'title' => $taskData['title'],
                'dt_complete_by' => $taskData['dt_complete_by'],
                'user_id' => $taskData['user_id'],
                'information' => $taskData['information'],
                'model_id' => $opportunity->id,
                'model_type' => $opportunity->getMorphClass(),
            ]);
            $opportunity->tasks()->save($task);
        }

        $this->form->model($opportunity)->saveRelationships();

        $this->form->fill();
        $this->dispatch('close-modal',  id: 'create-opportunity-modal-dropdown');
        $this->dispatch('close-modal',  id: 'create-opportunity-modal-kanban');
    }

    private function initializedFieldsNullCheck(): bool
    {
        // Checking if all of the fields except label_id are null
        // The reason for this is in the case where the user clicks on the + to add a label/company
        // After that, all of the previously data inputted is lost
        $excludedKeys = ['hideSelect', 'hideSelectAfterCompanyCreate', 'label_id', 'task.is_private'];

        $filteredData = collect($this->data)->filter(function ($value, $key) use ($excludedKeys) {
            if (in_array($key, $excludedKeys)) {
                return false;
            }

            if (is_array($value)) {
                $filteredNested = collect($value)->filter(function ($nestedValue, $nestedKey) use ($excludedKeys, $key) {
                    if (in_array("$key.$nestedKey", $excludedKeys)) {
                        return false;
                    }

                    return !is_null($nestedValue);
                });

                return !$filteredNested->isEmpty();
            }

            return !is_null($value);
        });

        return $filteredData->isEmpty();
    }

    public function render()
    {
        return view('livewire.opportunity-modal-form');
    }
}
