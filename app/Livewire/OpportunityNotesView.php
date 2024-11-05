<?php

namespace App\Livewire;

use App\Models\Note;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\User;
use App\Utils\Filament\Actions\OpportunityChildActionHelper;
use App\Utils\RichEditorButtons;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class OpportunityNotesView extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public $opportunityId;
    public $opportunity;
    public $notes = [];


    public function mount($opportunityId)
    {
        $this->opportunityId = $opportunityId;

        $this->opportunity = Opportunity::find($opportunityId);
    }

    #[Computed]
    public function notes()
    {
        return $this->opportunity?->notes()->orderBy('created_at', 'desc')->with('user')->get()->map(function ($note){
            $note->created_at = Carbon::parse($note->created_at)->timezone(auth()->user()->timezone);
            return $note;
        });
    }

    #[On('refreshNotes')]
    public function refreshNotes()
    {
        $this->opportunity->load('notes');
    }

    public function createNoteAction(): Action
    {
        return Action::make('createNote')
            ->form([
                RichEditor::make('note')->label(__('portal.notes_add'))
                    ->toolbarButtons(RichEditorButtons::$toolbarButtons)
                    ->columnSpanFull(),
                Toggle::make('add_task')
                    ->label(__('portal.notes.add_task'))
                    ->reactive()
                    ->dehydrated(false),
                Section::make(__('portal.tasks.task'))->schema([
                    TextInput::make('title')
                        ->label(__('portal.tasks.title'))
                        ->required()
                        ->autofocus()
                        ->columnSpanFull(),
                    Textarea::make('information')
                        ->rows(4)
                        ->label(__('portal.tasks.information'))
                        ->columnSpanFull(),
                    Grid::make(4)->schema([
                        Select::make('user_id')
                            ->label(__('portal.users.user'))
                            ->options(User::assignableUsers()->pluck('name', 'id'))
                            ->searchable()
                            ->default(auth()->id())
                            ->visible(fn() => auth()->user()->isAdmin())
                            ->columnSpan(2),
                        DatePicker::make('dt_complete_by')
                            ->label(__('portal.complete_by'))
                            ->columnSpan(2),
                    ])
                ])->reactive()->visible(fn($get) => $get('add_task'))->statePath('task')
            ])
            ->hiddenLabel()
            ->icon('heroicon-o-plus')
            ->action(function ($data){
                Note::create([
                    'note' => $data['note'],
                    'model_id' => $this->opportunityId,
                    'model_type' => Opportunity::class,
                ]);
                if(array_key_exists('task', $data) && $data['task']['title'] !== null){
                    Task::create([
                        'title' => $data['task']['title'],
                        'information' => $data['task']['information'],
                        'user_id' => $data['task']['user_id'],
                        'dt_complete_by' => $data['task']['dt_complete_by'],
                        'model_id' => $this->opportunityId,
                        'model_type' => Opportunity::class,
                    ]);
                }

                $this->dispatch('openKanbanEdit', [
                    'recordId' => $this->opportunityId,
                    'data' => $data
                ]);

                $this->dispatch('refreshTasks');
                $this->dispatch('refreshNotes');
            })
            ->modalHeading(OpportunityChildActionHelper::getModalHeading($this));
    }

    public function deleteNote($noteId)
    {
        $note = Note::where('id', $noteId)
            ->where('user_id', auth()->id())
            ->first();

        if ($note) {
            $note->delete();
            $this->dispatch('refreshNotes');
        }
    }

    public function render()
    {
        return view('livewire.opportunity-notes-view');
    }
}
