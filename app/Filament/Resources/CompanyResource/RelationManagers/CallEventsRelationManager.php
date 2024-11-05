<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Filament\Resources\CallEventResource;
use App\Livewire\CallEventDisplay;
use App\Models\CallEvent;
use App\Utils\CallDurationHelper;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CallEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'callEvents';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('call_type')
            ->columns(
                CallEventResource::getColumns(CallEvent::COMPANY)
            )
            ->actions(
                CallEventResource::getTableActions()
            )
            ->filters(CallEventResource::getFilters(CallEvent::COMPANY))
            ->modifyQueryUsing(function ($query) {
                $query->with(['company', 'answeredBy']);
            })
            ->recordAction(Tables\Actions\ViewAction::class)
            ->defaultSort('event_time', 'desc');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('portal.calls.calls');
    }
}
