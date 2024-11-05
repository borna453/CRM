<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Enums\Permissions;
use App\Models\PinboardItem;
use App\Models\User;
use App\Utils\RichEditorButtons;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PinboardItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'pinboardItems';

    protected $listeners = ['refreshPinboardItems' => '$refresh'];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('portal.users.contact'))
                    ->options(
                        User::regularUser()->where('company_id', $this->ownerRecord->id)->pluck('name', 'id')
                    )
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\RichEditor::make('description')
                    ->label(__('portal.description'))
                    ->required()
                    ->toolbarButtons(RichEditorButtons::$toolbarButtons)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->actions([
                Tables\Actions\EditAction::make()->modalHeading(fn($record) => strip_tags($record->description))->hiddenLabel()->icon('')->disabled(fn($record) => $record->createdBy?->id !== auth()->id()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->modalWidth('3xl')
            ])
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->label(__('portal.users.contact'))
                    ->formatStateUsing(function ($record){
                        return $record->user->name;
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('portal.description'))
                    ->html()
                    ->wrap(),
            ])
            ->recordAction(Tables\Actions\EditAction::class)
            ->modifyQueryUsing(fn($query) => $query->with(['user', 'createdBy']));
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('portal.pinboard_items.pinboard');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('portal.pinboard_items.empty_table_heading');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('viewAny', PinboardItem::class);
    }

    protected function canEdit(Model $record): bool
    {
        return true;
    }
}
