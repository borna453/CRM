<?php

namespace App\Filament\User\Resources;

use App\Enums\Features;
use App\Enums\Permissions;
use App\Filament\User\Resources\ReportResource\Pages;
use App\Infolists\Components\SpatieMediaLibraryDocumentEntry;
use App\Models\Appointment;
use App\Models\Feature;
use App\Models\Report;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('portal.reports.table_title'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment.dt_start')
                    ->label(__('portal.reports.table_date'))
                    ->searchable()
                    ->sortable()
                    ->date('d-m-Y'),
            ])
            ->actions([
                CommentsAction::make()->visible(fn() => auth()->user()->can('createComment', Report::class)),
            ])
            ->recordUrl(function (Report $record): string {
                if ($record->appointment && $record->user_id === $record->appointment->user_id && Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && auth()->user()->can('viewAny', Appointment::class)) {
                    return AppointmentResource::getUrl('view', ['record' => $record->appointment]);
                }

                return ReportResource::getUrl('view', ['record' => $record]);
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                    Section::make(__('portal.reports.details'))->schema([
                        TextEntry::make('title')
                            ->label(__('portal.reports.table_title'))
                            ->extraAttributes(['class' => 'border-b border-gray-300 pb-2.5']),
                        TextEntry::make('description')
                            ->label(__('portal.description'))
                            ->html()
                            ->formatStateUsing(fn($record) => $record->description)
                    ])->columnSpan(2),
                    Section::make(__('portal.attachments'))->schema([
                        SpatieMediaLibraryDocumentEntry::make('attachments')
                            ->label(__('portal.documents'))
                            ->collection('document-attachments')
                            ->extraAttributes(function ($record){
                                return $record->hasImages() ? ['class' => 'border-b border-gray-300 pb-8 mt-2.5 mb-2.5'] : [];
                            })
                        ->visible(fn($record) => $record->getMedia('document-attachments')->isNotEmpty()),
                        SpatieMediaLibraryImageEntry::make('attachments')
                            ->label(__('portal.images'))
                            ->collection('image-attachments')
                            ->visible(fn($record) => $record->getMedia('image-attachments')->isNotEmpty()),
                    ])->columnSpan(2)->visible(fn($record) => $record->getMedia('document-attachments')->isNotEmpty() || $record->getMedia('image-attachments')->isNotEmpty()),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'view' => Pages\ViewReport::route('/{record}/view'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return Report::with('appointment')
            ->whereVisibleTo(auth()->user())
            ->published()
            ->orderBy('created_at', 'desc');
    }

    public static function getModelLabel(): string
    {
        return __('portal.reports.report');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.reports.reports');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Report::class);
    }
}
