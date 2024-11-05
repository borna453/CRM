<?php

namespace App\Filament\User\Resources\TaskResource\Pages;

use App\Filament\User\Resources\TaskResource;
use App\Models\Task;
use App\Traits\OpensModalOnRedirect;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class ListTasks extends ListRecords
{
    use AdvancedTables;
    use OpensModalOnRedirect;

    protected static string $resource = TaskResource::class;

    public static function getPresetViews(): array
    {
        return [
            'open' => PresetView::make()->label(__('portal.open'))
                ->favorite()
                ->icon('heroicon-o-document')
                ->badge(Task::open()->whereAssignedTo(auth()->user())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->open())
                ->default(),
            'closed' => PresetView::make()->label(__('portal.closed'))
                ->favorite()
                ->icon('heroicon-o-check')
                ->badge(Task::completed()->whereAssignedTo(auth()->user())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('dt_is_completed', 'desc')->completed()),
            'deleted' => PresetView::make()->label(__('portal.deleted'))
                ->favorite()
                ->icon('heroicon-o-trash')
                ->badge(Task::onlyTrashed()->createdBy(auth()->user())->whereAssignedTo(auth()->user())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->createdBy(auth()->user())->onlyTrashed()->orderBy('deleted_at', 'desc')),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $this->mountHandlesModalOpening();
    }
}
