<?php

namespace App\Filament\User\Resources;

use App\Enums\Permissions;
use App\Filament\User\Resources\AppointmentResource\Pages;
use App\Infolists\Components\SpatieMediaLibraryDocumentEntry;
use App\Models\Appointment;
use App\Utils\AppointmentDateHelper;
use App\Utils\Notifications\AppointmentEventHelper;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 1;


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('portal.appointments.appointment'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dt_start')
                    ->label(__('portal.appointments.start'))
                    ->searchable()
                    ->sortable()
                    ->date('d-m-Y H:i')
                    ->formatStateUsing(fn($record) => AppointmentDateHelper::formatDateRange($record)),
            ])
            ->recordUrl(fn (Appointment $record): string => AppointmentResource::getUrl('view', ['record' => $record]));
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(4)->schema([
                    Section::make(__('portal.appointments.details'))->schema([
                        TextEntry::make('title')
                            ->label(__('portal.appointments.table_title'))
                            ->extraAttributes(['class' => 'border-b border-gray-300 pb-2.5']),
                        TextEntry::make('description')
                            ->label(__('portal.description'))
                            ->formatStateUsing(fn($record) => new HtmlString($record->description))
                            ->visible(fn($record) => $record->description)
                            ->extraAttributes(['class' => 'border-b border-gray-300 pb-2.5']),
                        TextEntry::make('dt_start')
                            ->label(__('portal.appointments.start'))
                            ->date('d-m-Y H:i')
                            ->formatStateUsing(fn($record) => AppointmentDateHelper::formatDateRange($record))
                            ->extraAttributes(function($record){
                                return $record->location ? ['class' => 'border-b border-gray-300 pb-2.5'] : [];
                            }),

                        TextEntry::make('location')
                            ->label(__('portal.appointments.location'))
                            ->visible(fn($record) => $record->location)
                            ->formatStateUsing(function ($record){
                                $location = AppointmentEventHelper::getLocationDetails($record, $record->location);
                                if (!empty($record->online_url) && $location === __('portal.appointments.online')) {
                                    $location .= ' - ' . $record->online_url;
                                }

                               return $location;
                            }),
                    ])->columnSpan(1),

                    Section::make(__('portal.reports.details'))->schema([
                        TextEntry::make('report.description')
                            ->label(__('portal.description'))
                            ->html()
                            ->formatStateUsing(fn($record) => $record->report?->description)
                            ->extraAttributes(function ($record){
                                return $record->report->hasDocuments() || $record->report->hasImages() ? ['class' => 'border-b border-gray-300 pb-2.5'] : [];
                            }),
                        SpatieMediaLibraryDocumentEntry::make('report.attachments')
                            ->label(__('portal.documents'))
                            ->collection('document-attachments')
                            ->extraAttributes(function ($record){
                                return $record->hasImages() ? ['class' => 'border-b border-gray-300 pb-2.5'] : [];
                            })
                            ->visible(function ($record){
                                return $record->hasDocuments();
                            }),
                        SpatieMediaLibraryImageEntry::make('report.attachments')
                            ->label(__('portal.images'))
                            ->collection('image-attachments')
                            ->visible(function ($record){
                                return $record->report->hasImages();
                            })
                            ->simpleLightbox(),
                    ])->columnSpan(3)->visible(fn($record) => $record->report),
                ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'view' => Pages\ViewAppointment::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Appointment::whereVisibleTo(auth()->user())->whereUpcoming()->count();

        return $count > 0 ? $count : null;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = Appointment::whereVisibleTo(auth()->user());
        $date = request()->query('date');

        if ($date) {
            $query->whereDate('dt_start', $date);
        }

        return $query;
    }

    public static function getModelLabel(): string
    {
        return __('portal.appointments.appointment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.appointments.appointments');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Appointment::class);
    }
}
