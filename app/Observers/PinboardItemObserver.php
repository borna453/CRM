<?php

namespace App\Observers;

use App\Models\PinboardItem;
use App\Utils\Filament\FormFields\MessageHelper;
use App\Utils\Filament\FormFields\RichEditorAttachmentsHelper;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Illuminate\Notifications\DatabaseNotification;

class PinboardItemObserver
{
    public function creating(PinboardItem $pinboardItem): void
    {
        if(auth()->user()->isUser()){
            $pinboardItem->user_id = auth()->id();
        }
        $pinboardItem->created_by = auth()->id();

        if($pinboardItem->description){
            $pinboardItem->description = RichEditorAttachmentsHelper::processContent($pinboardItem->description);
        }
    }

    public function created(PinboardItem $pinboardItem)
    {
        if(auth()->user()->isAdmin()){
            $pinboardItem->user->notify(new \App\Notifications\PinboardItemNotification($pinboardItem));

            event(new DatabaseNotificationsSent($pinboardItem->user));
        }
    }

    public function deleted(PinboardItem $pinboardItem)
    {
        DatabaseNotification::where('data->viewData->model_type', PinboardItem::class)
            ->where('data->viewData->model_id', $pinboardItem->id)
            ->delete();
    }
}
