<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallEventResource\Pages;
use App\Livewire\CallEventDisplay;
use App\Models\CallEvent;
use App\Models\Company;
use App\Models\User;
use App\Utils\CallDurationHelper;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class CallEventResource extends Resource
{
    protected static ?string $model = CallEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->query(CallEvent::query()->with(['company', 'answeredBy']))
            ->columns(
                self::getColumns()
            )
            ->actions(self::getTableActions())
            ->filters(self::getFilters(), layout: FiltersLayout::AboveContent)
            ->defaultSort('event_time', 'desc');
    }

    public static function getColumns($view = null): array
    {
        return [
            Tables\Columns\TextColumn::make('company_id')
                ->label(__('portal.companies.company'))
                ->formatStateUsing(fn($record) => $record->company?->name)
                ->visible(empty($view)),
            Tables\Columns\TextColumn::make('answered_by')
                ->formatStateUsing(fn($record) => $record->answeredBy?->name),
            Tables\Columns\TextColumn::make('to_number')
                ->label(__('portal.companies.phone_number'))
                ->formatStateUsing(function ($record){
                    return match ($record->call_type) {
                        CallEvent::INCOMING_CALL => $record->from_number,
                        CallEvent::OUTGOING_CALL => $record->to_number,
                        default => null,
                    };
                }),
            Tables\Columns\TextColumn::make('call_status')
                ->label(__('portal.calls.call_status'))
                ->badge(),
            Tables\Columns\TextColumn::make('call_type')
                ->label(__('portal.calls.call_type'))
                ->formatStateUsing(function ($record){
                    return match ($record->call_type) {
                        CallEvent::INCOMING_CALL => __('portal.calls.incoming_call'),
                        CallEvent::OUTGOING_CALL => __('portal.calls.outgoing_call'),
                        default => null,
                    };
                }),
            Tables\Columns\TextColumn::make('duration')
                ->label(__('portal.calls.duration'))
                ->formatStateUsing(function ($record){
                    return CallDurationHelper::formatDuration($record);
                }),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('portal.calls.call_start'))
                ->dateTime(),
        ];
    }

    public static function getFilters($view = null)
    {
        return [
            SelectFilter::make('company_id')
                ->visible(empty($view))
                ->label(__('portal.calls.company_id'))
                ->options(Company::pluck('name', 'id')),

            Filter::make('event_time')
                ->label(__('portal.calls.event_time'))
                ->form([
                    DatePicker::make('value')
                        ->label(__('portal.calls.event_time'))
                        ->date()
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                ])
                ->query(function ($query, array $data) {
                    if (empty($data['value'])) {
                        return $query;
                    }

                    return $query->whereDate('event_time', $data['value']);
                })
                ->indicateUsing(function (array $data): ?string {
                    if (empty($data['value'])) {
                        return null;
                    }

                    return 'Date: ' . Carbon::parse($data['value'])?->format('d-m-Y');
                }),

            SelectFilter::make('answered_by')
                ->label(__('portal.calls.answered_by'))
                ->options(User::pluck('name', 'id')),
        ];
    }

    public static function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make()->hiddenLabel()->modalHeading('')->icon('')->form(function ($record) {
                return [
                    Forms\Components\Livewire::make(CallEventDisplay::class, ['callEventId' => $record->id])
                        ->key(fn ($record) => 'call-event-display-' . $record->id),
                ];
            }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCallEvents::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('portal.calls.call');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.calls.calls');
    }
}
