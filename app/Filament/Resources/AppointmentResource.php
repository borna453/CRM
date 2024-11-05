<?php

namespace App\Filament\Resources;

use App\Enums\Features;
use App\Enums\LocationOptions;
use App\Enums\Permissions;
use App\Enums\ViewOptions;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\Widgets\CalendarWidget;
use App\Models\Appointment;
use App\Models\Feature;
use App\Models\User;
use App\Utils\AppointmentDateHelper;
use App\Utils\AppointmentValidatorHelper;
use App\Utils\Filament\Actions\RestoreActionHelper;
use App\Utils\RichEditorButtons;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $slug = 'appointments';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label(__('portal.users.contacts'))
                    ->options(User::role(User::USER)->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                Select::make('report_id')
                    ->label(__('portal.reports.report'))
                    ->options(function ($record) {
                        return \App\Models\Report::whereDoesntHave('appointment')
                            ->orWhere('id', $record?->report_id)
                            ->pluck('title', 'id');
                    })
                    ->preload()
                    ->searchable()
                    ->nullable()
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                TextInput::make('title')
                    ->label(__('portal.reports.table_title'))
                    ->required()
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                Select::make('location')
                    ->label(__('portal.location'))
                    ->options(LocationOptions::options())
                    ->default(LocationOptions::MY_LOCATION->value)
                    ->reactive()
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                TextInput::make('online_url')
                    ->label(__('portal.appointments.online_url'))
                    ->hidden(function(Get $get){
                        return $get('location') !== LocationOptions::ONLINE->value;
                    })
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                TextInput::make('other_location')
                    ->label(__('portal.appointments.other_location'))
                    ->hint(__('portal.appointments.other_location_hint'))
                    ->hidden(function(Get $get){
                        return $get('location') !== LocationOptions::OTHER->value;
                    })
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                DatePicker::make('date')
                    ->label(__('portal.date'))
                    ->date('d-m-Y')
                    ->native(false)
                    ->required()
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                TimePicker::make('start_time')
                    ->label(__('portal.start_time'))
                    ->time()
                    ->seconds(false)
                    ->required()
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                TimePicker::make('end_time')
                    ->label(__('portal.end_time'))
                    ->time()
                    ->seconds(false)
                    ->required()
                    ->rules([
                        fn(Get $get, $record): Closure => function ($value, $attribute, Closure $fail) use ($get, $record) {
                            AppointmentValidatorHelper::validateOverlappingTimes($value, $attribute, $fail, $get, $record);
                        }
                    ])
                    ->after('start_time')
                    ->validationMessages([
                        'after' => __('portal.appointments.end_time_after_start_time')
                    ])
                    ->columnStart(2)
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                    RichEditor::make('description')
                        ->label(__('portal.description'))
                        ->columnStart(1)
                        ->toolbarButtons(RichEditorButtons::$toolbarButtons)
                        ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                    RichEditor::make('internal_notes')
                        ->label(__('portal.appointments.internal_notes'))
                        ->toolbarButtons(RichEditorButtons::$toolbarButtons)
                        ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('portal.users.contacts'))
                    ->options(User::role(User::USER)->pluck('name', 'id')),
                Filter::make('start')
                    ->label(__('portal.date'))
                    ->form([
                        DatePicker::make('value')
                            ->label(__('portal.date'))
                            ->date()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereDate('dt_start', $data['value']);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['value'])) {
                            return null;
                        }

                        return 'Date: ' . Carbon::parse($data['value'])?->format('d-m-Y');
                    })
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                EditAction::make()->visible(function($record){
                    return !$record->deleted_at;
                }),
                RestoreActionHelper::restoreAction()->hidden(fn() => !auth()->user()->can('restore', Appointment::class)),
            ])
            ->recordUrl(function (Appointment $record) {
                if($record->trashed()){
                    return null;
                }

                return AppointmentResource::getUrl('view', ['record' => $record]);
            });
    }

    public static function getColumns($view = null): array
    {
        $columns = [
            TextColumn::make('user.name')
                ->label(__('portal.users.contacts'))
                ->searchable()
                ->sortable(),
            TextColumn::make('title')
                ->label(__('portal.reports.table_title'))
                ->searchable()
                ->sortable(),
            TextColumn::make('dt_start')
                ->label(__('portal.date_time'))
                ->sortable()
                ->date('d-m-Y H:i')
                ->formatStateUsing(fn($record) => AppointmentDateHelper::formatDateRange($record))
        ];

        if(empty($view)){
            $columns[] = TextColumn::make('invoiced_at')->label(__('portal.appointments.invoiced_at'))->visible(fn() => Feature::isActive(Features::ADMINISTRATION) && auth()->user()->can('viewUnbilled', Appointment::class));
        }
        return $columns;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\CreateAppointment::route('/'),
            'list' => Pages\ListAppointments::route('/list'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
            'view' => Pages\ViewAppointment::route('/{record}/view'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return Appointment::with('user');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Appointment::whereUpcoming()->count();

        return $count > 0 ? $count : null;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.appointments.appointments');
    }

    public static function getWidgets(): array
    {
        return [
            CalendarWidget::make(),
        ];
    }
    public static function getBreadcrumb(): string
    {
        return __('portal.appointments.appointments');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.appointments.appointments');
    }

    public static function getModelLabel(): string
    {
        return __('portal.appointments.appointment');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Appointment::class);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('update', Appointment::class);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('delete', Appointment::class);
    }

    public static function getDocumentation(): array|string
    {
        return [
            'appointment',
        ];
    }
}
