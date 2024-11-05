<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Livewire\ConversationModalView;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Livewire;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeableColumn::make('created_by')
                    ->label('Recipient')
                    ->formatStateUsing(fn($record) => (new HtmlString('<strong>' . ($record->creator?->name ??'') . '</strong>')))
                    ->suffixBadges([
                        Badge::make('user_unread_replies_count')
                            ->label(fn($record) => $record->user_unread_replies_count)
                            ->color('primary')
                            ->visible(fn($record) => $record->user_unread_replies_count > 0),
                    ]),
                TextColumn::make('last_reply_time')
                    ->label(__('portal.messages.reply'))
                    ->badge()
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->diffForHumans() : 'No replies'),
                TextColumn::make('unread_replies_count')
                    ->label('Unread Replies')
                    ->alignCenter()
                    ->badge(),
            ])
            ->defaultGroup(
                Group::make('parentMessage.title')
                    ->label(__('portal.messages.message'))
                    ->getTitleFromRecordUsing(fn($record) => $record->parentMessage?->title ?? '')
                    ->collapsible(),
            )
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form(function ($record) {
                        return [
                            Livewire::make(ConversationModalView::class, ['messageId' => $record?->parentMessage?->id, 'conversationId' => $record?->id, 'userId' => $record?->created_by]),
                        ];
                    })
                    ->modalFooterActions([
                        Tables\Actions\Action::make('markAsCompleted')
                            ->button()
                            ->action(function ($record, $livewire) {
                                $record->update(['dt_is_completed' => now()]);
                                $livewire->dispatch('confetti');
                            })
                            ->label(__('portal.messages.mark_as_completed'))
                            ->visible(function ($record) {
                                return !$record->dt_is_completed;
                            }),
                        Action::make('close')
                            ->color('gray')
                            ->button()
                            ->label(__('portal.close'))
                            ->close()
                    ])
                    ->modalWidth('5xl')
                    ->modalHeading(''),
            ])
            ->modifyQueryUsing(function ($query) {
                $query->with('messages', 'messages.recipients', 'parentMessage', 'creator')
                      ->whereHas('messages.tenant');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $unreadCount = Message::whereNull('seen_at')
            ->whereHas('creator', function ($query) {
                $query->whereHas('roles', function ($query) {
                    $query->where('name', User::USER);
                });
            })
            ->count();


        return $unreadCount > 0 ? (string) $unreadCount : null;
    }

    public static function getModelLabel(): string
    {
        return __('portal.messages.conversation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.messages.conversations');
    }
}
