<?php

namespace App\Filament\Resources\CallEventResource\Pages;

use App\Filament\Resources\CallEventResource;
use App\Models\CallEvent;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCallEvents extends ListRecords
{
    use AdvancedTables;

    protected static string $resource = CallEventResource::class;

    public function getPresetViews(): array
    {
        return [
            'all' => PresetView::make(__('portal.all'))
                ->badge(CallEvent::count())
                ->default()
                ->favorite()
                ->icon('heroicon-o-phone'),
            'incoming' => PresetView::make(__('portal.calls.incoming_calls'))
                ->badge(CallEvent::incomingCalls()->count())
                ->modifyQueryUsing(fn($query) => $query->incomingCalls())
                ->favorite()
                ->icon('heroicon-o-phone-arrow-down-left'),
            'outgoing' => PresetView::make(__('portal.calls.outgoing_calls'))
                ->badge(CallEvent::outgoingCalls()->count())
                ->modifyQueryUsing(fn($query) => $query->outgoingCalls())
                ->favorite()
                ->icon('heroicon-o-phone-arrow-up-right'),
        ];
    }
}
