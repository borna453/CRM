<?php

namespace App\Filament\User\Resources\AppointmentResource\Pages;

use App\Filament\User\Resources\AppointmentResource;
use App\Models\Appointment;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Closure;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAppointments extends ListRecords
{
    use AdvancedTables;

    protected static string $resource = AppointmentResource::class;

    public function getPresetViews(): array
    {
        return [
            'upcoming' => PresetView::make('Upcoming')
                ->label(__('portal.upcoming'))
                ->favorite()
                ->icon('heroicon-o-clock')
                ->badge(Appointment::whereUpcoming()->whereVisibleTo(auth()->user())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereUpcoming()->whereVisibleTo(auth()->user())->orderBy('dt_start', 'asc'))
                ->default(),
            'past' => PresetView::make('Past')
                ->label(__('portal.past'))
                ->favorite()
                ->icon('heroicon-o-calendar')
                ->badge(Appointment::wherePast()->whereVisibleTo(auth()->user())->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->wherePast()->whereVisibleTo(auth()->user())->orderBy('dt_start', 'desc')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('history')->url(route('filament.user.pages.user-appointment-history'))->label(__('portal.appointments.history'))->visible(function(){
                return Appointment::hasPublishedReports();
            }),
        ];
    }
}
