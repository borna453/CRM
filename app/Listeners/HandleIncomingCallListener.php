<?php

namespace App\Listeners;

use App\Models\CallEvent;
use App\Utils\RinkelHelper;
use Carbon\Carbon;
use SaloonRinkel\Events\IncomingCallEvent;

class HandleIncomingCallListener extends BaseRinkelListener
{
    public function handle(IncomingCallEvent $event): void
    {
        response()->json(['status' => 'success'])->send();

        if (!$this->checkKey()|| !$this->checkId($event->incomingCallDTO->id)) {
            return;
        }

        $this->setNotification('IncomingCall',  (string)$event->incomingCallDTO);

        CallEvent::create([
            'call_id' => $event->incomingCallDTO->id,
            'tenant_id' => RinkelHelper::findTenantId(),
            'company_id' => RinkelHelper::findCompanyId($event->incomingCallDTO->from),
            'to_number' => $event->incomingCallDTO->to,
            'from_number' => $event->incomingCallDTO->from,
            'event_time' => Carbon::parse($event->incomingCallDTO->datetime)->format('Y-m-d H:i:s'),
            'call_type' => CallEvent::INCOMING_CALL,
        ]);
    }

}
