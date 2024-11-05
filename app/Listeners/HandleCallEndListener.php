<?php

namespace App\Listeners;

use App\Models\CallEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use SaloonRinkel\Events\CallEndEvent;

class HandleCallEndListener extends BaseRinkelListener
{
    public function handle(CallEndEvent $event): void
    {
        response()->json(['status' => 'success'])->send();

        if (!$this->checkKey()|| !$this->checkId($event->callEndDTO->id)) {
            return;
        }

        $this->setNotification('CallEnd',  (string)$event->callEndDTO);
        try {
            $callEvent = CallEvent::withoutTenancy()->where('call_id', $event->callEndDTO->id)->firstOrFail();

            $callEvent->updateOrFail([
                'duration' => Carbon::parse($callEvent->event_time)->diffInSeconds(Carbon::parse($event->callEndDTO->datetime)),
                'call_status' => $event->callEndDTO->cause,
            ]);
        } catch (ModelNotFoundException $e) {
            \Log::error("Call event not found for call_id: {$event->callEndDTO->id}");
        } catch (\Throwable $e) {
            \Log::error("Error updating call event for call_id: {$event->callEndDTO->id}. Error: " . $e->getMessage());
        }
    }
}
