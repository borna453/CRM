<?php

namespace App\Observers;

use App\Models\Message;
use App\Models\Recipient;
use App\Models\User;
use App\Notifications\MessageNotification;
use App\Utils\Filament\FormFields\MessageHelper;
use App\Utils\Filament\FormFields\RichEditorAttachmentsHelper;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Events\DatabaseNotificationsSent;

class MessageObserver
{
    public function creating(Message $message):void
    {
        $message->created_by = auth()->id();

        if($message->content){
            $message->content = RichEditorAttachmentsHelper::processContent($message->content);
        }
    }
}
