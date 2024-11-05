<?php

namespace App\Filament\User\Resources;

use App\Enums\Permissions;
use App\Filament\User\Resources\MessageResource\Pages;
use App\Livewire\ConversationModalView;
use App\Livewire\MessageModalView;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\User;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\DatabaseNotification;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeableColumn::make('title')
                    ->label(__('portal.messages.title'))
                    ->formatStateUsing(fn($record) => (new HtmlString('<strong>' . $record->title . '</strong>')))
                    ->suffixBadges([
                        Badge::make('admin_unread_replies_count')
                            ->label(fn($record) => $record->replies()->first()?->conversation?->admin_unread_replies_count)
                            ->color('primary')
                            ->visible(fn($record) => $record->replies()->first()?->conversation?->admin_unread_replies_count > 0),
                    ]),
                Tables\Columns\TextColumn::make('created_by')
                    ->formatStateUsing(fn(Message $record) => $record->creator->name . ', ' . Carbon::parse($record->created_at)->diffForHumans())
                    ->label(__('portal.sent_by')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->form(function ($record, $livewire){
                    $livewire->dispatch('refreshEngagementWidget');
                    Recipient::where('message_id', $record?->id)->where('user_id', auth()->id())->update(['seen_at' => now()]);
                    $notification = \Illuminate\Notifications\DatabaseNotification::where('data->viewData->model_id', $record?->id)->where('notifiable_id', auth()->id())->first();
                    $livewire->dispatch('close-notification', id: $notification?->id);
                     return [
                            Forms\Components\Livewire::make(ConversationModalView::class, ['messageId' => $record?->id,'conversationId' => $record?->conversation_id, 'userId' => auth()->id()]),
                        ];
                    })
                    ->modalFooterActions([
                        Tables\Actions\Action::make('markAsCompleted')
                            ->button()
                            ->action(function ($record, $livewire){
                                $record->replies()->first()->conversation->update(['dt_is_completed' => now()]);
                                $livewire->dispatch('confetti');
                            })
                            ->label(__('portal.messages.mark_as_completed'))
                            ->visible(fn($record) => $record->replies()->first()?->conversation && !$record->replies()->first()->conversation->dt_is_completed),
                        Action::make('close')
                            ->color('gray')
                            ->button()
                            ->label(__('portal.close'))
                            ->close()
                    ])
                    ->modalWidth('5xl')
                    ->modalHeading(''),
            ])
            ->recordAction(Tables\Actions\EditAction::class)
            ->modifyQueryUsing(function (Builder $query) {
                $query->forUser()->with(['creator', 'replies', 'conversation'])->orderBy('created_at', 'desc');
            });
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Recipient::where('user_id', auth()->id())->whereNull('seen_at')->count();
        return $count > 0 ? $count : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessages::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Message::class);
    }

    public static function getNavigationLabel(): string
    {
        return __('portal.messages.messages');
    }

}
