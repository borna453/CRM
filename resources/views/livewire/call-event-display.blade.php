<div>
    @if($callEvent)
        <div class="p-4 mb-4 border rounded-lg shadow-sm bg-white dark:bg-gray-800 dark:border-gray-700 flex flex-col">
            <div class="pt-2 pb-2 break-all">
                <p class="text-gray-800 dark:text-gray-200 mb-1">
                    <strong>{{ __('portal.calls.call') }}
                        ID:</strong> {{ $callEvent->call_id }}<br>
                    <strong>{{ __('portal.calls.company_id') }}
                        :</strong> {{ $callEvent->company?->name }}<br>
                    <strong>{{ __('portal.calls.answered_by') }}
                        :</strong> {{ $callEvent->answeredBy?->name }}<br>
                    <strong>{{ __('portal.calls.to') }}
                        :</strong> {{ $callEvent->to_number }}<br>
                    <strong>{{ __('portal.calls.from') }}
                        :</strong> {{ $callEvent->from_number }}<br>
                    <strong>{{ __('portal.calls.event_time') }}
                        :</strong> {{ \Carbon\Carbon::parse($callEvent->event_time)->format('d-m-Y H:i') }}
                    <br>
                    <strong>{{ __('portal.calls.call_type') }}:</strong> {{ match ($callEvent->call_type){
                        \App\Models\CallEvent::INCOMING_CALL => __('portal.calls.incoming_call'),
                        \App\Models\CallEvent::OUTGOING_CALL => __('portal.calls.outgoing_call'),
                    } }}<br>
                    <strong>{{ __('portal.calls.call_status') }}
                        :</strong> {{ $callEvent->call_status }}<br>
                    <strong>{{ __('portal.calls.duration') }}
                        :</strong> {{ \App\Utils\CallDurationHelper::formatDuration($callEvent) }}
                    <br>
                    <strong>{{ 'Sentiment' }}
                        :</strong> {{ $callEvent->insights_summary }}<br>
                    <strong>{{ __('portal.calls.insights_summary') }}
                        :</strong> {{ $callEvent->insights_summary }}<br>
                </p>
                <div class="text-sm text-gray-500 dark:text-gray-400 self-end text-right">
                    <p>{{ \Carbon\Carbon::parse($callEvent->event_time)->format('d-m-Y H:i') }}</p>
                </div>
            </div>
        </div>
    @else
        <p class="text-gray-500 dark:text-gray-400">{{ __('portal.calls.call_not_found') }}</p>
    @endif
</div>
