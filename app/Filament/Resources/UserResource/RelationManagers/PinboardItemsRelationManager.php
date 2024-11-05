<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\Permissions;
use App\Filament\User\Resources\PinboardItemsResource;
use App\Models\PinboardItem;
use App\Utils\Filament\Actions\PinboardItemsActionHelper;
use App\Utils\RichEditorButtons;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PinboardItemsRelationManager extends RelationManager
{
    use AdvancedTables;

    protected static string $relationship = 'pinboardItems';

    protected $listeners = ['refreshPinboardItems' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns(PinboardItemsResource::getColumns())
            ->headerActions([
                CreateAction::make()
                    ->label(__('portal.pinboard_items.create'))
                    ->modalHeading(__('portal.pinboard_items.create'))
                    ->form(PinboardItemsResource::getFormSchema())
                    ->after(function ($record){
                        $record->update([
                            'user_id' => $this->ownerRecord->id,
                        ]);
                    })
            ])
            ->actions([
                EditAction::make()->label('')->icon('')
                    ->modalHeading(__('portal.pinboard_items.edit'))
                    ->form(PinboardItemsResource::getFormSchema())
                    ->extraModalFooterActions([
                        Action::make('complete')
                            ->label(__('portal.complete'))
                            ->color('success')
                            ->close()
                            ->action(function ($record) {
                                $record->complete();
                            }),
                    ])->disabled(fn($record) => $record->createdBy->id !== auth()->id()),
                PinboardItemsActionHelper::completeAction(),
                PinboardItemsActionHelper::uncompleteAction(),
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->recordAction(function ($record){
                return !$record->dt_is_completed ? EditAction::class : null;
            })
            ->paginated(false)
            ->modifyQueryUsing(function (Builder $query){
                $query->with('createdBy');
            })
            ->emptyStateDescription(__('portal.pinboard_items.empty_table_description'))
            ->emptyStateHeading(__('portal.pinboard_items.empty_table_heading'));
    }

    public function getPresetViews(): array
    {
        return [
            'open' => PresetView::make('Open')
                ->label(__('portal.open'))
                ->favorite()
                ->icon('heroicon-o-document')
                ->badge(PinboardItem::open()->whereUserId($this->ownerRecord->id)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->open()->orderBy('created_at', 'desc'))
                ->default(),
            'closed' => PresetView::make('Closed')
                ->label(__('portal.closed'))
                ->favorite()
                ->icon('heroicon-o-check')
                ->badge(PinboardItem::completed()->whereUserId($this->ownerRecord->id)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->completed()->orderBy('created_at', 'desc')),
        ];
    }

    public function defaultPresetViewShouldBeApplied(): bool
    {
        return true;
    }

    protected static function getModelLabel(): ?string
    {
        return __('portal.pinboard_items.pinboard_item');
    }

    public static function getPluralLabel(): ?string
    {
        return __('portal.pinboard_items.pinboard_items');
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('portal.pinboard_items.pinboard');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('portal.pinboard_items.pinboard');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('viewAny', PinboardItem::class);
    }
}
