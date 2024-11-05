<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\LocationOptions;
use App\Enums\Permissions;
use App\Models\Appointment;
use App\Traits\DateMutateFieldsTrait;
use App\Utils\AppointmentDateHelper;
use App\Utils\AppointmentValidatorHelper;
use App\Utils\RichEditorButtons;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AppointmentsRelationManager extends RelationManager
{
    use AdvancedTables;
    use DateMutateFieldsTrait;

    protected static string $relationship = 'appointments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label(__('portal.reports.table_title'))
                    ->columnSpanFull()
                    ->autofocus()
                    ->required(),
                DatePicker::make('date')
                    ->label(__('portal.date'))
                    ->columnSpanFull()
                    ->date('d-m-Y')
                    ->native(false)
                    ->required(),
                Select::make('location')
                    ->label(__('portal.location'))
                    ->columnSpanFull()
                    ->options(LocationOptions::options())
                    ->default(LocationOptions::MY_LOCATION->value)
                    ->reactive()
                    ->required(),
                TextInput::make('online_url')
                    ->label(__('portal.appointments.online_url'))
                    ->columnSpanFull()
                    ->hidden(function(Get $get){
                        return $get('location') !== LocationOptions::ONLINE->value;
                    }),
                TextInput::make('other_location')
                    ->label(__('portal.appointments.other_location'))
                    ->columnSpanFull()
                    ->hint(__('portal.appointments.other_location_hint'))
                    ->hidden(function(Get $get){
                        return $get('location') !== LocationOptions::OTHER->value;
                    }),
                TimePicker::make('start_time')
                    ->label(__('portal.start_time'))
                    ->columnSpanFull()
                    ->time()
                    ->seconds(false)
                    ->required(),
                TimePicker::make('end_time')
                    ->label(__('portal.end_time'))
                    ->columnSpanFull()
                    ->time()
                    ->rules([
                        fn(Get $get): Closure => function ($value, $attribute, Closure $fail) use ($get) {
                            AppointmentValidatorHelper::validateOverlappingTimes($value, $attribute, $fail, $get);
                        }
                    ])
                    ->after('start_time')
                    ->validationMessages([
                        'after' => __('portal.appointments.end_time_after_start_time')
                    ])
                    ->seconds(false)
                    ->required(),
                RichEditor::make('description')
                    ->label(__('portal.description'))
                    ->columnSpanFull()
                    ->columnStart(1)
                    ->toolbarButtons(RichEditorButtons::$toolbarButtons),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label(__('portal.reports.table_title'))
                    ->sortable(),
                TextColumn::make('dt_start')
                    ->label(__('portal.date_time'))
                    ->sortable()
                    ->date('d-m-Y H:i')
                    ->formatStateUsing(fn($record) => AppointmentDateHelper::formatDateRange($record))
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalWidth(MaxWidth::FiveExtraLarge)
                    ->mutateFormDataUsing(function ($data): array {
                        return $this->combineDateFields($data);
                    })
                    ->createAnother(false),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->fillForm(function ($record){
                        return [
                            'title' => $record->title,
                            'date' => $record->dt_start->format('d-m-Y'),
                            'location' => $record->location,
                            'start_time' => $record->dt_start->format('H:i'),
                            'end_time' => $record->dt_end->format('H:i'),
                            'description' => $record->description,
                        ];
                    }),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->paginated(false)
            ->emptyStateHeading(__('portal.appointments.no_appointments'))
            ->emptyStateDescription(__('portal.appointments.no_appointments_description'));
    }

    public function getPresetViews(): array
    {
        return [
            'upcoming' => PresetView::make('Upcoming')
                ->label(__('portal.upcoming'))
                ->favorite()
                ->icon('heroicon-o-clock')
                ->badge(Appointment::whereUpcoming()->whereUserId($this->ownerRecord->id)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereUserId($this->ownerRecord->id)->whereUpcoming())
                ->default(),
            'past' => PresetView::make('Past')
                ->label(__('portal.past'))
                ->favorite()
                ->icon('heroicon-o-calendar')
                ->badge(Appointment::wherePast()->whereUserId($this->ownerRecord->id)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->wherePast()->whereUserId($this->ownerRecord->id)),
            'deleted' => PresetView::make('Deleted')
                ->label(__('portal.deleted'))
                ->favorite()
                ->icon('heroicon-o-trash')
                ->badge(Appointment::onlyTrashed()->whereUserId($this->ownerRecord->id)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()->whereUserId($this->ownerRecord->id)),
        ];
    }

    public function defaultPresetViewShouldBeApplied(): bool
    {
        return true;
    }

    protected static function getModelLabel(): ?string
    {
        return __('portal.appointments.appointment');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('portal.appointments.appointments');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('portal.appointments.appointments');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('viewAny', Appointment::class);
    }
}
