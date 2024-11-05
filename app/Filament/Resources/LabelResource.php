<?php

namespace App\Filament\Resources;

use App\Enums\LabelTypes;
use App\Enums\Permissions;
use App\Enums\PrimaryColor;
use App\Enums\ViewOptions;
use App\Filament\Resources\LabelResource\Pages;
use App\Models\Label;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LabelResource extends Resource
{
    protected static ?string $model = Label::class;

    protected static ?string $slug = 'labels';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label(__('portal.labels.name'))
                    ->sortable(),
                TextColumn::make('color')
                    ->badge()
                    ->formatStateUsing(fn ($record) => PrimaryColor::from($record->color)->getLabel())
                    ->label(__('portal.labels.color'))
                    ->color(fn ($record) => $record->color),

                ToggleColumn::make('show_on_board')
                    ->disabled(fn() => !auth()->user()->can('update', Label::class))
                    ->label(__('portal.labels.show_on_board')),
                ToggleColumn::make('should_archive')
                    ->disabled(fn() => !auth()->user()->can('update', Label::class))
                    ->label(__('portal.labels.should_archive')),
                ToggleColumn::make('finished_state')
                    ->disabled(fn() => !auth()->user()->can('update', Label::class))
                    ->label(__('portal.labels.create_choice')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->reorderable('order_column')
            ->paginated(false);
    }

    public static function getFormSchema($view = null): array
    {
        $schema = [
            Grid::make()->schema([
                TextInput::make('name')
                    ->label(__('portal.labels.name'))
                    ->required()
                    ->columns(1),
                Select::make('color')
                    ->label(__('portal.labels.color'))
                    ->columns(1)
                    ->allowHtml()
                    ->native(false)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->default(PrimaryColor::Green->value)
                    ->options(
                        collect(PrimaryColor::cases())
                            ->mapWithKeys(static fn ($case) => [
                                $case->value => "<span class='flex items-center gap-x-4'>
                                <span class='rounded-full w-4 h-4' style='background:rgb(" . $case->getColor()[600] . ")'></span>
                                <span>" . $case->getLabel() . '</span>
                                </span>',
                            ]),
                    ),
                Grid::make(3)->schema([
                    Toggle::make('should_archive')
                        ->label(__('portal.labels.should_archive'))
                        ->columns(1)
                        ->default(false),
                    Toggle::make('finished_state')
                        ->label(__('portal.labels.create_choice'))
                        ->columns(1)
                        ->default(false),
                    Toggle::make('show_on_board')
                        ->label(__('portal.labels.show_on_board'))
                        ->visible(empty($view))
                        ->columns(1)
                        ->default(true),
                ])
                    ->columns(3)
                    ->visible(function ($livewire) use($view) {
                        if(empty($view)){
                            return $livewire->activePresetView === LabelTypes::Opportunity->value;
                        }
                        return true;
                    })
                    ->reactive(),
            ])
        ];

        return $schema;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLabels::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('portal.labels.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.labels.labels');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Label::class);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', Label::class);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update', Label::class);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('delete', Label::class);
    }

    public static function canReorder(): bool
    {
        return auth()->user()->can('reorder', Label::class);
    }
}
