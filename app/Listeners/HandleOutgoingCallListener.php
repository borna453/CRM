<?php

namespace App\Listeners;

use App\Models\CallEvent;
use App\Utils\RinkelHelper;
use Carbon\Carbon;
use SaloonRinkel\Events\OutgoingCallEvent;

class HandleOutgoingCallListener extends BaseRinkelListener
{
    public function handle(OutgoingCallEvent $event): void
    {
        response()->json(['status' => 'success'])->send();

        if (!$this->checkKey()|| !$this->checkId($event->outgoingCallDTO->id)) {
            return;
        }

        $this->setNotification('OutgoingCall',  (string)$event->outgoingCallDTO);

        CallEvent::create([
            'call_id' => $event->outgoingCallDTO->id,
            'tenant_id' => RinkelHelper::findTenantId(),
            'company_id' => RinkelHelper::findCompanyId($event->outgoingCallDTO->to),
            'to_number' => $event->outgoingCallDTO->to,
            'from_number' => $event->outgoingCallDTO->from,
            'event_time' => Carbon::parse($event->outgoingCallDTO->datetime)->format('Y-m-d H:i:s'),
            'call_type' => CallEvent::OUTGOING_CALL,
        ]);
    }
}
