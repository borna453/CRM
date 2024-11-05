<?php

namespace App\Filament\Resources;

use App\Enums\Permissions;
use App\Enums\ViewOptions;
use App\Filament\Resources\ReportResource\Pages;
use App\Filament\Resources\ReportResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Company;
use App\Models\Report;
use App\Models\User;
use App\Utils\Filament\Actions\RestoreActionHelper;
use App\Utils\RichEditorButtons;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.company')
                    ->label(__('portal.companies.company'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($record){
                        return $record->user->company->name;
                    }),
                TextColumn::make('user.name')
                    ->label(__('portal.client'))
                    ->searchable()
                    ->sortable(),
                BadgeableColumn::make('title')
                    ->label(__('portal.reports.table_title'))
                    ->suffixBadges([
                        Badge::make('unpublished')
                            ->label(__('portal.reports.unpublished'))
                            ->color('gray')
                            ->visible(fn($record) => is_null($record->published_at)),
                    ])
                    ->action(Action::make('publish')
                        ->label(__('portal.reports.publish'))
                        ->requiresConfirmation(function ($record) {
                            return !$record->is_published;
                        })
                        ->action(function ($record) {
                            return match (true) {
                                !$record->is_published => $record->publish(),
                                !is_null($record->appointment) => redirect(AppointmentResource::getUrl('view', ['record' => $record->appointment])),
                                default => redirect(ReportResource::getUrl('edit', ['record' => $record])),
                            };
                        }))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('appointment.dt_start')
                    ->label(__('portal.date'))
                    ->sortable()
                    ->date('d-m-Y'),
            ])
            ->filters([
                Filter::make('unpublished')
                    ->label(__('portal.reports.unpublished'))
                    ->query(fn(Builder $query) => $query->whereNull('published_at')),

                Filter::make('company_id')
                    ->form([
                        Select::make('user.company_id')
                            ->label(__('portal.companies.company'))
                            ->options(Company::all()->pluck('name', 'id'))
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['user']['company_id']) {
                            $companyId = $data['user']['company_id'];
                            $query->whereHas('user.company', function ($query) use ($companyId) {
                                $query->where('id', $companyId);
                            });
                        }
                        return $query;
                    })
            ])
            ->actions([
                EditAction::make()->visible(function ($record) {
                    return is_null($record->deleted_at);
                }),
                RestoreActionHelper::restoreAction()->hidden(fn() => !auth()->user()->can('restore', Report::class)),
            ])
            ->recordUrl(function ($record) {
                if (!is_null($record->deleted_at)) {
                    return null;
                }

                if ($record->appointment && auth()->user()->can('viewAny', Appointment::class)) {
                    return AppointmentResource::getUrl('view', ['record' => $record->appointment]);
                }

                if (auth()->user()->can('viewAny', Report::class)) {
                    return ReportResource::getUrl('edit', ['record' => $record]);
                }
            })
            ->defaultSort('appointment.dt_start', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return Report::with(['appointment', 'user', 'user.company'])
            ->whereVisibleTo(auth()->user());
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TaskRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    public static function getFormSchema($view = null, $ownerRecord = null): array
    {
        return [
            Select::make('user_id')
                ->label(__('portal.client'))
                ->options(User::role(User::USER)->pluck('name', 'id'))
                ->searchable()
                ->visible(fn() => empty($view))
                ->reactive()
                ->required(),
            Select::make('appointment_id')
                ->label(__('portal.appointments.appointment'))
                ->options(function ($get) use($ownerRecord){
                    if($ownerRecord){
                        return Appointment::where('user_id', $ownerRecord->id)
                            ->whereNull('report_id')
                            ->get()
                            ->mapWithKeys(fn($appointment) => [
                                $appointment->id => Carbon::parse($appointment->dt_start)->format('Y-m-d') . ' - ' . $appointment->title
                            ]);
                    }
                    if ($get('user_id')) {
                        return Appointment::where('user_id', $get('user_id'))
                            ->whereNull('report_id')
                            ->get()
                            ->mapWithKeys(fn($appointment) => [
                                $appointment->id => Carbon::parse($appointment->dt_start)->format('Y-m-d') . ' - ' . $appointment->title
                            ]);
                    }

                    return Appointment::whereNull('report_id')
                        ->get()
                        ->mapWithKeys(fn($appointment) => [
                            $appointment->id => Carbon::parse($appointment->dt_start)->format('Y-m-d') . ' - ' . $appointment->title
                        ]);
                })
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    $appointment = Appointment::find($state);

                    if ($appointment) {
                        $set('user_id', $appointment->user_id);
                    }
                })
                ->nullable(),
            TextInput::make('title')
                ->label(__('portal.reports.table_title'))
                ->required(),
            Section::make([
                // The reason this is fakedescription field, is because when 'description' is used, the richeditor wont work. Only in the case of 'description'.
                RichEditor::make('fakedescription')
                    ->label(__('portal.description'))
                    ->toolbarButtons(RichEditorButtons::$toolbarButtons)
                    ->formatStateUsing(fn($record) => $record?->description)
                    ->required(),
            ]),
            Section::make([
                SpatieMediaLibraryFileUpload::make('image-attachments')
                    ->label(__('portal.images'))
                    ->multiple()
                    ->image()
                    ->reorderable()
                    ->collection('image-attachments')
                    ->live(debounce: 10000),
                SpatieMediaLibraryFileUpload::make('document-attachments')
                    ->label(__('portal.documents'))
                    ->multiple()
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->reorderable()
                    ->collection('document-attachments')
                    ->live(debounce: 10000),
            ]),

            Hidden::make('published_at')->default(null)
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Report::toPublish()->count();

        return $count > 0 ? $count : null;
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.reports.reports');
    }

    public static function getBreadcrumb(): string
    {
        return __('portal.reports.reports');
    }

    public static function getModelLabel(): string
    {
        return __('portal.reports.report');
    }

    public static function getPluralLabel(): ?string
    {
        return __('portal.reports.reports');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Report::class);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', Report::class);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update', Report::class);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('delete', Report::class);
    }

    public static function getDocumentation(): array|string
    {
        return [
            'report',
        ];
    }
}
