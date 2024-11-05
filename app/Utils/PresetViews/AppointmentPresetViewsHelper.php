<?php

namespace App\Utils\PresetViews;

use App\Models\Appointment;
use Archilex\AdvancedTables\Components\PresetView;
use Illuminate\Database\Eloquent\Builder;

class AppointmentPresetViewsHelper
{
    public static function appointmentPresetViews(): array
    {
        return [
            'upcoming' => PresetView::make('Upcoming')
                ->label(__('portal.upcoming'))
                ->favorite()
                ->icon('heroicon-o-clock')
                ->badge(Appointment::whereUpcoming()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereUpcoming()->orderBy('dt_start', 'asc'))
                ->default(),
            'past' => PresetView::make('Past')
                ->label(__('portal.past'))
                ->favorite()
                ->icon('heroicon-o-calendar')
                ->badge(Appointment::wherePast()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->wherePast()->orderBy('dt_start', 'desc')),
            'deleted' => PresetView::make('Deleted')
                ->label(__('portal.deleted'))
                ->favorite()
                ->icon('heroicon-o-trash')
                ->badge(Appointment::onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()->orderBy('deleted_at', 'desc')),
        ];
    }
}
