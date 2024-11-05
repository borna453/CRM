<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use App\Models\PinboardItem;
use App\Utils\Notifications\NotificationButtonHelper;
use App\Utils\Notifications\NotificationHelper;
use Filament\Notifications\Actions\Action;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PinboardItemNotification extends BaseNotification implements ShouldQueue
{
    public function __construct(
        private readonly PinboardItem $pinboardItem
    )
    {
        $this->message = __('portal.notifications.pinboard_item.created');
        parent::__construct(['pinboardItem' => $this->pinboardItem]);
    }

    protected function getType(): string
    {
        return NotificationTypeEnum::PINBOARD_ITEM_CREATED->value;
    }

    public function via($notifiable): array
    {
        return NotificationHelper::getNotificationChannels($notifiable);
    }

    public function toDatabase($notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title(__('portal.notifications.pinboard_item.subject_created'))
            ->body($this->message . ' ' . strip_tags($this->pinboardItem->description))
            ->actions([
                Action::make('openModal')
                    ->label(__('portal.notifications.pinboard_item.view'))
                    ->url(NotificationHelper::generateTenantUrl($this->pinboardItem->tenant_id, 'filament.user.resources.pinboard-items.index', ['model_id' => $this->pinboardItem->id])),
            ])
            ->viewData(['model_id' => $this->pinboardItem->id, 'model_type' => PinboardItem::class])
            ->getDatabaseMessage();
    }

    public function templateParams($buttonText = null, $preview = false): array
    {
        $buttonLabel = $buttonText ?? __('portal.notifications.pinboard_item.view');

        if(!$preview){
            $pinboardUrl = NotificationHelper::generateTenantUrl(
                $this->pinboardItem->tenant?->id,
                'filament.user.resources.pinboard-items.index',
                ['model_id' => $this->pinboardItem->id]
            );
        }

        return [
            'omschrijving' => $this->pinboardItem->description ?? '',
            'creator' => $this->pinboardItem->createdBy->name ?? '',
            'button' => NotificationButtonHelper::generateButtonHtml($buttonLabel, $pinboardUrl ?? '#'),
            ...$this->getTenantEmailInformation($this->pinboardItem)
        ];
    }

    public static function templateParamsList(): array
    {
        return [
            'omschrijving' => __('portal.notifications.pinboard_item.params.description'),
            'creator' => __('portal.notifications.pinboard_item.params.creator'),
            'button' => __('portal.notifications.pinboard_item.params.button'),
            ...self::getTenantEmailTemplateInformation()
        ];
    }

    public static function fake(): Notification
    {
        $notification = null;

        self::safelyFake(function () use (&$notification) {
            $notification = new self(PinboardItem::factory()::preview([
                'description' => 'Test omschrijving',
                'created_by' => auth()->user(),
            ]));
        });

        return $notification;
    }
}
