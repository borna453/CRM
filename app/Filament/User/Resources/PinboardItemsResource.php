<?php

namespace App\Filament\User\Resources;

use App\Enums\Permissions;
use App\Filament\User\Resources\PinboardItemsResource\Pages;
use App\Models\PinboardItem;
use App\Utils\Filament\Actions\PinboardItemsActionHelper;
use App\Utils\Filament\Actions\RestoreActionHelper;
use App\Utils\RichEditorButtons;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PinboardItemsResource extends Resource
{
    protected static ?string $model = PinboardItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?int $navigationSort= 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function (){
                return PinboardItem::query()->whereVisibleTo(auth()->user());
            })
            ->columns(self::getColumns())
            ->actions([
                Tables\Actions\EditAction::make()->label('')->icon('')
                    ->modalHeading(__('portal.pinboard_items.edit'))
                    ->extraModalFooterActions([
                        Tables\Actions\Action::make('complete')
                            ->label(__('portal.complete'))
                            ->color('success')
                            ->close()
                            ->action(function ($record) {
                                $record->complete();
                            }),
                        DeleteAction::make()->successRedirectUrl(url()->previous())
                    ]),
                PinboardItemsActionHelper::completeAction()->hidden(fn() => !auth()->user()->can('update', PinboardItem::class)),
                PinboardItemsActionHelper::uncompleteAction()->hidden(fn() => !auth()->user()->can('update', PinboardItem::class)),
                RestoreActionHelper::restoreAction()
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->recordAction(function ($record){
                if(!auth()->user()->can('update', PinboardItem::class)){
                    return null;
                }

                return !$record->deleted_at && !$record->dt_is_completed ? Tables\Actions\EditAction::class : null;
            })
            ->modifyQueryUsing(function (Builder $query){
                $query->with('createdBy');
            })
            ->emptyStateHeading(__('portal.pinboard_items.empty_table_heading'))
            ->emptyStateDescription(__('portal.pinboard_items.empty_table_description'));
    }

    public static function getColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('description')
                ->label(__('portal.description'))
                ->html()
                ->wrap(),
            TextColumn::make('created_by')
                ->label(__('portal.created_by'))
                ->formatStateUsing(fn($record) => $record->createdBy->name),
        ];
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\RichEditor::make('description')
                ->label(__('portal.description'))
                ->required()
                ->toolbarButtons(RichEditorButtons::$toolbarButtons)
                ->columnSpanFull(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPinboardItems::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('portal.pinboard_items.pinboard_item');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.pinboard_items.pinboard');
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.pinboard_items.pinboard');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', PinboardItem::class);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', PinboardItem::class);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update', PinboardItem::class);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('delete', PinboardItem::class);
    }
}
