<?php

namespace App\Filament\Owner\Resources\NotificationTemplateResource\Pages;

use App\Enums\NotificationTypeEnum;
use App\Enums\Permissions;
use App\Filament\Owner\Resources\NotificationTemplateResource;
use App\Models\NotificationTemplate;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class EditNotificationTemplate extends EditRecord
{
    protected static string $resource = NotificationTemplateResource::class;

    public function getHeading(): string|Htmlable
    {
        return NotificationTypeEnum::from($this->record->type)->label();
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if(filament()->getCurrentPanel()?->getId() === User::ADMIN && is_null($record->tenant_id)){
            $notificationTemplate = NotificationTemplate::create([
                'type' => $record->type,
                'email_subject' => $data['email_subject'] ?? null,
                'email_content' => $data['email_content'] ?? null,
                'button_text' => $data['button_text'] ?? null,
                'tenant_id' => tenant()?->id,
            ]);

            $this->redirect(url(NotificationTemplateResource::getUrl('edit', ['record' => $notificationTemplate])));

            return $notificationTemplate;
        }

        $recordToUpdate = parent::handleRecordUpdate($record, $data);

        if(filament()->getCurrentPanel()?->getId() === User::ADMIN && !is_null($record->tenant_id)) {
            $record->update($data);
        }

        return $recordToUpdate;
    }

    public static function canAccess(array $parameters = []): bool
    {
        if(filament()->getCurrentPanel()?->getId() === User::ADMIN){
            return auth()->user()->can('update', NotificationTemplate::class);
        }

        return true;
    }
}
