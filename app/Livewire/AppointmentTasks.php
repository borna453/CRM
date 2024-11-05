<?php

namespace App\Livewire;

use App\Models\Task;
use App\Utils\Filament\Actions\CompleteActionHelper;
use App\Utils\Filament\Actions\UncompleteActionHelper;
use App\Utils\Filament\Tables\TaskIconHelper;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Livewire\Component;

class AppointmentTasks extends Component implements HasActions, HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    public $appointmentId;
    public $reportId;

    public function table(Table $table): Table
    {
        return $table
            ->query(Task::query()->whereUserId(auth()->id())->associatedWithModel($this->appointmentId, $this->reportId))
            ->columns([
               TextColumn::make('title')
                   ->label(__('portal.tasks.title')),
                TextColumn::make('dt_complete_by')
                    ->label(__('portal.complete_by'))
                    ->date('d-m-Y')
                    ->icon(fn($record) => TaskIconHelper::getIcon($record))
                    ->iconColor(fn($record) => TaskIconHelper::getIconColor($record)),
            ])
            ->actions([
                CompleteActionHelper::completeAction(),
                UncompleteActionHelper::uncompleteAction()
            ], position: ActionsPosition::BeforeColumns)
            ->paginated(false);
    }

    public function getTaskCount()
    {
        return Task::query()->whereUserId(auth()->id())->associatedWithModel($this->appointmentId, $this->reportId)->count();
    }

    public function render()
    {
        return view('livewire.appointment-tasks');
    }
}
