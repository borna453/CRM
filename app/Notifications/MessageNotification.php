<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use App\Utils\Notifications\NotificationHelper;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Message $message,
        private $recipient)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title($this->message->title)
            ->actions([
                Action::make('openMessage')
                    ->label(__('portal.messages.view_message'))
                    ->url(NotificationHelper::generateTenantUrl($this->message->tenant_id, 'filament.user.resources.messages.index', ['model_id' => $this->message->id])),
            ])
            ->viewData(['model_id' => $this->message->id, 'model_type' => Message::class])
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
