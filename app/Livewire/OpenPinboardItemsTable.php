<?php

namespace App\Livewire;

use App\Filament\User\Resources\PinboardItemsResource;
use App\Models\PinboardItem;
use App\Utils\Filament\Actions\PinboardItemsActionHelper;
use App\Utils\RichEditorButtons;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;

class OpenPinboardItemsTable extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $appointment;

    public function table(Table $table): Table
    {
        return $table->query(PinboardItem::query()->open()->whereUserId($this->appointment->user->id)->with('createdBy'))
            ->columns(PinboardItemsResource::getColumns())
            ->headerActions([
                CreateAction::make()
                    ->label(__('portal.pinboard_items.create'))
                    ->modalHeading(__('portal.pinboard_items.create'))
                    ->modalWidth('3xl')
                    ->form(PinboardItemsResource::getFormSchema())->action(function ($data){
                        $data['user_id'] = $this->appointment->user->id;
                        PinboardItem::create($data);
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
                    ]),
                PinboardItemsActionHelper::completeAction(),
                PinboardItemsActionHelper::uncompleteAction(),
            ], position: ActionsPosition::BeforeColumns)
            ->recordAction(EditAction::class)
            ->paginated(false)
            ->emptyStateHeading(__('portal.pinboard_items.empty_table_heading'))
            ->emptyStateDescription(__('portal.pinboard_items.empty_table_description'));
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('portal.pinboard_items.pinboard');
    }

    public function render()
    {
        return view('livewire.open-pinboard-items-table');
    }
}
