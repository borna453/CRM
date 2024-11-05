<?php

namespace App\Filament\Resources;

use App\Enums\Permissions;
use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Livewire\MessageModalView;
use App\Livewire\RecipientList;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?int $navigationSort = 5;
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('created_by')
                    ->formatStateUsing(fn($record) => $record->creator->name . ', ' . Carbon::parse($record->created_at)->diffForHumans())
                    ->label(__('portal.sent_by'))
                    ->alignCenter(),
                TextColumn::make('view_count')
                    ->label(__('portal.messages.seen_by'))
                    ->badge()
                    ->alignCenter(),
                TextColumn::make('recipients_count')
                    ->label(__('portal.messages.recipients'))
                    ->badge()
                    ->alignCenter(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->form(function ($record){
                    return [
                        Forms\Components\Livewire::make(MessageModalView::class, ['messageId' => $record?->id]),
                        Forms\Components\Section::make()->heading(function ($record){
                            $seenCount = Recipient::where('message_id', $record->id)
                                ->with('user')
                                ->whereNotNull('seen_at')
                                ->count();

                            $heading = __('portal.messages.recipients');
                            $seenBy = __('portal.messages.seen_by');

                            return new HtmlString("
                                <div class='flex justify-between items-center'>
                                    <h2 class='text-lg font-semibold text-gray-900 dark:text-white'>
                                        $heading
                                        <span class='text-sm font-normal text-gray-600 dark:text-gray-400 ml-2'>
                                            ($seenBy: $seenCount)
                                        </span>
                                    </h2>
                                </div>
                            ");
                        })->collapsible()->collapsed()->schema(function ($record){
                            return [
                                  Forms\Components\Livewire::make(RecipientList::class, ['messageId' => $record->id]),
                            ];
                        })
                    ];
                })
                    ->modalWidth('3xl')
                    ->modalHeading(''),
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['creator', 'recipients'])->whereNull('parent_id')->orderBy('created_at', 'desc');
            })
            ->emptyStateHeading(__('portal.messages.empty_state_heading'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessages::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('portal.messages.message');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.messages.messages');
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.messages.messages');
    }


    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Message::class);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', Message::class);
    }
}
