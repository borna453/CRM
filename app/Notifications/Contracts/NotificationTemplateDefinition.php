<?php

namespace App\Notifications\Contracts;

use App\Enums\NotificationTypeEnum;
use Illuminate\Notifications\Notification;

interface NotificationTemplateDefinition
{
    public static function templateType(): NotificationTypeEnum;

    public function templateParams($buttonText): array;

    public static function fake(): Notification;
}
