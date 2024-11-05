<?php

namespace App\Traits;

use App\Models\NotificationTemplate;

trait GetEmailContentTrait
{
    protected function getEmailModel($type)
    {
        $notification = NotificationTemplate::where('type', $type)->withoutTenancy()->whereNull('tenant_id')->first();

        if ($tenantNotification = NotificationTemplate::where('type', $type)->first()) {
            $notification = $tenantNotification;
        }
        return $notification;
    }

    public function getEmailContent()
    {
        $notification = $this->getEmailModel($this->getType());

        $params = $this->templateParams($notification->button_text);

        return $this->replaceVariables($notification->email_content, $params);
    }
}
