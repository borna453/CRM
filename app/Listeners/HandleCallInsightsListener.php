<?php

namespace App\Listeners;

use App\Models\CallEvent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use SaloonRinkel\Events\CallInsightsEvent;

class HandleCallInsightsListener extends BaseRinkelListener
{
    public function handle(CallInsightsEvent $event): void
    {
        response()->json(['status' => 'success'])->send();

        if (!$this->checkKey()|| !$this->checkId($event->callInsightsDTO->id)) {
            return;
        }

        $this->setNotification('CallInsights',  (string)$event->callInsightsDTO);

        try {
            CallEvent::withoutTenancy()->where('call_id', $event->callInsightsDTO->id)->firstOrFail()->updateOrFail([
                'sentiment_indicator' => $event->callInsightsDTO->sentiment,
                'insights_summary' => $event->callInsightsDTO->summary,
            ]);
        } catch (ModelNotFoundException $e) {
            \Log::error("Call event not found for call_id: {$event->callInsightsDTO->id}");
            return;
        } catch (\Throwable $e) {
            \Log::error("Error updating call event: " . $e->getMessage());
            return;
        }
    }
}
