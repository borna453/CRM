<?php

namespace App\Listeners;

use App\Models\CallEvent;
use App\Utils\RinkelHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use SaloonRinkel\Events\CallStartEvent;

class HandleCallStartListener extends BaseRinkelListener
{
    public function handle(CallStartEvent $event): void
    {
        response()->json(['status' => 'success'])->send();

        if (!$this->checkKey() || !$this->checkId($event->callStartDTO->id)) {
            return;
        }

        $this->setNotification('CallStart',  (string)$event->callStartDTO);

        try {
            CallEvent::withoutTenancy()->where('call_id', $event->callStartDTO->id)->firstOrFail()->updateOrFail([
                'answered_by' => RinkelHelper::findAnsweredBy($event->callStartDTO->answeredBy),
            ]);
        } catch (ModelNotFoundException $e) {
            \Log::error("Call event not found for call_id: {$event->callStartDTO->id}");
            return;
        } catch (\Throwable $e) {
            \Log::error("Error updating call event: " . $e->getMessage());
            return;
        }
    }
}
