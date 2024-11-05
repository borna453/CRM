<?php

namespace App\Utils\PresetViews;

use App\Models\Task;
use Archilex\AdvancedTables\Components\PresetView;
use Illuminate\Database\Eloquent\Builder;

class TasksPresetViewsHelper
{
    public static function presetViews(): array
    {
        return [
            'open' => PresetView::make()->label(__('portal.open'))
                ->favorite()
                ->icon('heroicon-o-document')
                ->badge(Task::whereVisibleTo(auth()->user())->open()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereVisibleTo(auth()->user())->open())
                ->default(),
            'closed' => PresetView::make()->label(__('portal.closed'))
                ->favorite()
                ->icon('heroicon-o-check')
                ->badge(Task::whereVisibleTo(auth()->user())->completed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereVisibleTo(auth()->user())->completed()->orderBy('dt_is_completed', 'desc')),
            'deleted' => PresetView::make()->label(__('portal.deleted'))
                ->favorite()
                ->icon('heroicon-o-trash')
                ->badge(Task::onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereVisibleTo(auth()->user())->onlyTrashed()->orderBy('deleted_at', 'desc')),
        ];
    }
}
