<?php

namespace App\Filament\User\Widgets;

use App\Enums\Permissions;
use App\Filament\User\Resources\PinboardItemsResource;
use App\Models\PinboardItem;
use App\Utils\Filament\Actions\PinboardItemsActionHelper;
use App\Utils\RichEditorButtons;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class OpenPinboardItems extends BaseWidget
{
    protected $listeners = ['refreshPinboardItemsWidget' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(PinboardItem::open()->whereUserId(auth()->id())->with('createdBy'))
            ->columns(PinboardItemsResource::getColumns())
            ->headerActions([
                Action::make('view_all')
                    ->label(__('portal.view_all'))
                    ->outlined()
                    ->color('secondary')
                    ->url(fn() => PinboardItemsResource::getUrl())
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('')->icon('')
                    ->form(PinboardItemsResource::getFormSchema())
                    ->modalHeading(__('portal.pinboard_items.edit'))
                    ->extraModalFooterActions([
                        Action::make('complete')
                            ->color('success')
                            ->close()
                            ->label(__('portal.complete'))
                            ->hidden(fn($record) => !is_null($record->deleted_at))
                            ->visible(function ($record) {
                                return !$record->dt_is_completed &&
                                    ($record->user_id === auth()->id() || $record->user_id === null);
                            })
                            ->action(function ($record, $livewire) {
                                $record->complete();

                                $livewire->dispatch('confetti');
                            }),
                        DeleteAction::make(),
                    ]),
                PinboardItemsActionHelper::completeAction(),
                PinboardItemsActionHelper::uncompleteAction(),
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->emptyStateHeading(__('portal.pinboard_items.empty_table_heading'))
            ->emptyStateDescription(__('portal.pinboard_items.empty_table_description'))
            ->recordAction(Tables\Actions\EditAction::class)
            ->paginated(false);
    }

    public function getColumnSpan(): int|string|array
    {
        return [
            '2xl' => 1,
            'xl' => 2,
        ];
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('portal.pinboard_items.pinboard');
    }

    public static function canView(): bool
    {
        return auth()->user()->hasTenantPermissionTo(Permissions::VIEW_USER_OPEN_PINBOARD_ITEMS->value);
    }
}
