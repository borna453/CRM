<?php

use App\Listeners\HandleCallEndListener;
use App\Listeners\HandleCallInsightsListener;
use App\Listeners\HandleCallStartListener;
use App\Listeners\HandleIncomingCallListener;
use App\Listeners\HandleOutgoingCallListener;
use App\Models\CallEvent;
use App\Models\Company;
use App\Models\Tenant;
use App\Utils\RinkelHelper;
use Illuminate\Support\Facades\Event;
use SaloonRinkel\DataTransferObjects\HandleCallEndDTO;
use SaloonRinkel\DataTransferObjects\HandleCallInsightsDTO;
use SaloonRinkel\DataTransferObjects\HandleCallStartDTO;
use SaloonRinkel\DataTransferObjects\HandleIncomingCallDTO;
use SaloonRinkel\DataTransferObjects\HandleOutgoingCallDTO;
use SaloonRinkel\Events\CallEndEvent;
use SaloonRinkel\Events\CallInsightsEvent;
use SaloonRinkel\Events\CallStartEvent;
use SaloonRinkel\Events\IncomingCallEvent;
use SaloonRinkel\Events\OutgoingCallEvent;

it('can handle incoming call webhook', function () {
    Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    $company = Company::factory()->create([
        'phone_number' => '+31850609001',
    ]);

    Event::fake();

    $tenant = Tenant::findOrFail(RinkelHelper::findTenantId());

    $response = $this->postJson('/webhooks/rinkel/incoming-call?key='. $tenant->rinkel, [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'datetime' => '2023-06-14T13:56:24.969Z',
        'to' => '+31850609000',
        'from' => '+31850609001',
    ]);

    $response->assertStatus(200);



    (new HandleIncomingCallListener())->handle(new IncomingCallEvent(
        new HandleIncomingCallDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:56:24.969Z',
            to: '+31850609000',
            from: '+31850609001'
        )
    ));
    Event::assertDispatched(IncomingCallEvent::class, function ($event) {
        return $event->incomingCallDTO->id === '1c8b83a7c690084224fd3984515bc1a2';
    });
    $this->assertDatabaseHas('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_type' => CallEvent::INCOMING_CALL,
        'tenant_id' => 'cloudmazing-interactive-crm',
        'company_id' => $company->id,
    ]);
});


it('can handle outgoing call webhook', function () {
     Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Company::factory()->create([
        'phone_number' => '+31850609001',
    ]);

    Event::fake();

    $tenant = Tenant::findOrFail(RinkelHelper::findTenantId());

    $response = $this->postJson('/webhooks/rinkel/outgoing-call?key='. $tenant->rinkel, [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'datetime' => '2023-06-14T13:56:24.969Z',
        'to' => '+31850609000',
        'from' => '+31850609001',
    ]);

    $response->assertStatus(200);



    (new HandleOutgoingCallListener())->handle(new OutgoingCallEvent(
        new HandleOutgoingCallDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:56:24.969Z',
            to: '+31850609000',
            from: '+31850609001'
        )
    ));
    Event::assertDispatched(OutgoingCallEvent::class, function ($event) {
        return $event->outgoingCallDTO->id === '1c8b83a7c690084224fd3984515bc1a2';
    });
    $this->assertDatabaseHas('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_type' => CallEvent::OUTGOING_CALL,
    ]);
});

it('can handle call start webhook', function () {
    Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    $tenant = Tenant::findOrFail(RinkelHelper::findTenantId());

    CallEvent::create([
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_type' => CallEvent::INCOMING_CALL,
        'to_number' => '+31850609000',
        'from_number' => '+31850609001',
        'tenant_id' => $tenant->id,
        'company_id' => $this->company->id,
        'event_time' => '2023-06-14 13:56:24',
    ]);


    $response = $this->withHeaders([
        'Content-Type' => 'application/json',
    ])->postJson('/webhooks/rinkel/call-start?key='. $tenant->rinkel, [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'datetime' => '2023-06-14T13:57:00.000Z',
        'answeredBy' => 'admin@cloudmazing.nl',
    ]);

    $response->assertStatus(200);

    Event::assertDispatched(CallStartEvent::class, function ($event) {
        return $event->callStartDTO->id === '1c8b83a7c690084224fd3984515bc1a2';
    });

    (new HandleCallStartListener())->handle(new CallStartEvent(
        new HandleCallStartDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:57:00.000Z',
            answeredBy: 'admin@cloudmazing.nl',
            choice: 1
        )
    ));

    $this->assertDatabaseHas('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'answered_by' => $this->adminUser->id,
    ]);
});

it('can handle call end webhook', function () {
    Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    $tenant = Tenant::findOrFail(RinkelHelper::findTenantId());

    CallEvent::create([
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_type' => CallEvent::INCOMING_CALL,
        'to_number' => '+31850609000',
        'from_number' => '+31850609001',
        'tenant_id' => $this->tenant->id,
        'company_id' => $this->company->id,
        'event_time' => '2023-06-14 13:56:24',
    ]);

    $response = $this->postJson('/webhooks/rinkel/call-end?key='. $tenant->rinkel, [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'datetime' => '2023-06-14T14:00:00.000Z',
        'cause' => 'ANSWERED',
    ]);

    $response->assertStatus(200);

    (new HandleCallEndListener())->handle(new CallEndEvent(
        new HandleCallEndDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T14:00:00.000Z',
            cause: 'ANSWERED',
        )
    ));

    Event::assertDispatched(CallEndEvent::class, function ($event) {
        return $event->callEndDTO->id === '1c8b83a7c690084224fd3984515bc1a2';
    });



    $this->assertDatabaseHas('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_status' => 'ANSWERED',
        'duration' => 216,
    ]);
});

it('can handle call insights webhook', function () {
    Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    $tenant = Tenant::findOrFail(RinkelHelper::findTenantId());

    CallEvent::create([
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_type' => CallEvent::INCOMING_CALL,
        'to_number' => '+31850609000',
        'from_number' => '+31850609001',
        'tenant_id' => $this->tenant->id,
        'company_id' => $this->company->id,
        'event_time' => '2023-06-14 13:56:24',
    ]);

    $response = $this->postJson('/webhooks/rinkel/call-insights?key='. $tenant->rinkel, [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'sentiment' => 'POSITIVE',
        'summary' => 'This is a summary of the call.',
    ]);

    $response->assertStatus(200);

    Event::assertDispatched(CallInsightsEvent::class, function ($event) {
        return $event->callInsightsDTO->id === '1c8b83a7c690084224fd3984515bc1a2';
    });

    (new HandleCallInsightsListener())->handle(new CallInsightsEvent(
        new HandleCallInsightsDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            sentiment: 'POSITIVE',
            topics: [
                'sales',
                'support'
            ],
            summary: 'This is a summary of the call.',
        )
    ));

    $this->assertDatabaseHas('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'insights_summary' => 'This is a summary of the call.',
    ]);
});

it('does not create duplicate call events on receiving duplicate webhook', function () {
    Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Company::factory()->create([
        'phone_number' => '+31850609001',
    ]);

    $tenant = Tenant::findOrFail(RinkelHelper::findTenantId());

    $data = [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'datetime' => '2023-06-14T13:56:24.969Z',
        'to' => '+31850609000',
        'from' => '+31850609001',
    ];

    Event::fake();

    $this->postJson('/webhooks/rinkel/incoming-call?key='. $tenant->rinkel, $data);
    $this->postJson('/webhooks/rinkel/incoming-call?key='. $tenant->rinkel, $data);

    Event::assertDispatched(IncomingCallEvent::class, function ($event) {
        return $event->incomingCallDTO->id === '1c8b83a7c690084224fd3984515bc1a2';
    });

    (new HandleIncomingCallListener())->handle(new IncomingCallEvent(
        new HandleIncomingCallDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:56:24.969Z',
            to: '+31850609000',
            from: '+31850609001'
        )
    ));

    $this->assertDatabaseCount('call_events', 1);
});

it('rejects webhook for incoming call when key is missing', function () {
    Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    $this->postJson('/webhooks/rinkel/incoming-call', [
        'id' => '1c8b83a7c690084224ec3984515bc1a2',
        'datetime' => '2023-06-14T13:56:24.969Z',
        'to' => '+31850609000',
        'from' => '+31850609001',
    ]);

    (new HandleIncomingCallListener())->handle(new IncomingCallEvent(
        new HandleIncomingCallDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:56:24.969Z',
            to: '+31850609000',
            from: '+31850609001'
        )
    ));

    $this->assertDatabaseMissing('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
    ]);
});

it('rejects webhook for incoming call when key is invalid', function () {
    Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    $this->postJson('/webhooks/rinkel/incoming-call?key=123456789', [
        'id' => '1c8b83a7c690084224ec3984515bc1a2',
        'datetime' => '2023-06-14T13:56:24.969Z',
        'to' => '+31850609000',
        'from' => '+31850609001',
    ]);

    (new HandleIncomingCallListener())->handle(new IncomingCallEvent(
        new HandleIncomingCallDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:56:24.969Z',
            to: '+31850609000',
            from: '+31850609001'
        )
    ));

    $this->assertDatabaseMissing('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
    ]);
});

it('rejects webhook for outgoing call when key is missing', function () {
    Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    $this->postJson('/webhooks/rinkel/outgoing-call', [
        'id' => '1c8b83a7c690084224ec3984515bc1a2',
        'datetime' => '2023-06-14T13:56:24.969Z',
        'to' => '+31850609000',
        'from' => '+31850609001',
    ]);

    (new HandleOutgoingCallListener())->handle(new OutgoingCallEvent(
        new HandleOutgoingCallDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:56:24.969Z',
            to: '+31850609000',
            from: '+31850609001'
        )
    ));

    $this->assertDatabaseMissing('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
    ]);
});

it('rejects webhook for outgoing call when key is invalid', function () {
    Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    $this->postJson('/webhooks/rinkel/outgoing-call?key=123456789', [
        'id' => '1c8b83a7c690084224ec3984515bc1a2',
        'datetime' => '2023-06-14T13:56:24.969Z',
        'to' => '+31850609000',
        'from' => '+31850609001',
    ]);

    (new HandleOutgoingCallListener())->handle(new OutgoingCallEvent(
        new HandleOutgoingCallDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:56:24.969Z',
            to: '+31850609000',
            from: '+31850609001'
        )
    ));

    $this->assertDatabaseMissing('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
    ]);
});

it('rejects webhook for call start when key is missing', function () {
    $tenant = Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    CallEvent::create([
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_type' => CallEvent::INCOMING_CALL,
        'to_number' => '+31850609000',
        'from_number' => '+31850609001',
        'tenant_id' => $tenant->id,
        'company_id' => $this->company->id,
        'event_time' => '2023-06-14 13:56:24',
    ]);

    $this->withHeaders([
        'Content-Type' => 'application/json',
    ])->postJson('/webhooks/rinkel/call-start', [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'datetime' => '2023-06-14T13:57:00.000Z',
        'answeredBy' => 'admin@cloudmazing.nl',
    ]);

    (new HandleCallStartListener())->handle(new CallStartEvent(
        new HandleCallStartDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:57:00.000Z',
            answeredBy: 'admin@cloudmazing.nl',
            choice: 1
        )
    ));

    $this->assertDatabaseMissing('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'answered_by' => $this->adminUser->id,
    ]);
});

it('rejects webhook for call start when key is invalid', function () {
    $tenant = Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    CallEvent::create([
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_type' => CallEvent::INCOMING_CALL,
        'to_number' => '+31850609000',
        'from_number' => '+31850609001',
        'tenant_id' => $tenant->id,
        'company_id' => $this->company->id,
        'event_time' => '2023-06-14 13:56:24',
    ]);

    $this->withHeaders([
        'Content-Type' => 'application/json',
    ])->postJson('/webhooks/rinkel/call-start?key=123456789', [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'datetime' => '2023-06-14T13:57:00.000Z',
        'answeredBy' => 'admin@cloudmazing.nl',
    ]);

    (new HandleCallStartListener())->handle(new CallStartEvent(
        new HandleCallStartDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:57:00.000Z',
            answeredBy: 'admin@cloudmazing.nl',
            choice: 1
        )
    ));

    $this->assertDatabaseMissing('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'answered_by' => $this->adminUser->id,
    ]);
});

it('rejects webhook for call end when key is missing', function () {
    $tenant = Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    CallEvent::create([
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_type' => CallEvent::OUTGOING_CALL,
        'to_number' => '+31850609000',
        'from_number' => '+31850609001',
        'tenant_id' => $tenant->id,
        'company_id' => $this->company->id,
        'event_time' => '2023-06-14 13:56:24',
    ]);

    $this->withHeaders([
        'Content-Type' => 'application/json',
    ])->postJson('/webhooks/rinkel/call-end', [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'datetime' => '2023-06-14T13:57:00.000Z',
        'answeredBy' => 'admin@cloudmazing.nl',
    ]);

    (new HandleCallEndListener())->handle(new CallEndEvent(
        new HandleCallEndDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:57:00.000Z',
            cause: 'ANSWERED',
        )
    ));

    $this->assertDatabaseMissing('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_status' => 'ANSWERED',
        'duration' => 216,
    ]);
});

it('rejects webhook for call end when key is invalid', function () {
    $tenant = Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    CallEvent::create([
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_type' => CallEvent::INCOMING_CALL,
        'to_number' => '+31850609000',
        'from_number' => '+31850609001',
        'tenant_id' => $tenant->id,
        'company_id' => $this->company->id,
        'event_time' => '2023-06-14 13:56:24',
    ]);

    $this->withHeaders([
        'Content-Type' => 'application/json',
    ])->postJson('/webhooks/rinkel/call-end?key=123456789', [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'datetime' => '2023-06-14T13:57:00.000Z',
        'answeredBy' => 'admin@cloudmazing.nl',
    ]);

    (new HandleCallEndListener())->handle(new CallEndEvent(
        new HandleCallEndDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            datetime: '2023-06-14T13:57:00.000Z',
            cause: 'ANSWERED',
        )
    ));

    $this->assertDatabaseMissing('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_status' => 'ANSWERED',
        'duration' => 216,
    ]);
});

it('rejects webhooks for call insights when key is missing', function () {
    $tenant = Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    CallEvent::create([
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_type' => CallEvent::INCOMING_CALL,
        'to_number' => '+31850609000',
        'from_number' => '+31850609001',
        'tenant_id' => $tenant->id,
        'company_id' => $this->company->id,
        'event_time' => '2023-06-14 13:56:24',
    ]);

    $this->postJson('/webhooks/rinkel/call-insights', [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'sentiment' => 'POSITIVE',
        'summary' => 'This is a summary of the call.',
    ]);

    (new HandleCallInsightsListener())->handle(new CallInsightsEvent(
        new HandleCallInsightsDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            sentiment: 'POSITIVE',
            topics: [
                'sales',
                'support'
            ],
            summary: 'This is a summary of the call.',
        )
    ));

    $this->assertDatabaseMissing('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'insights_summary' => 'This is a summary of the call.',
    ]);
});

it('rejects webhooks for call insights when key is invalid', function () {
    $tenant = Tenant::factory()->create([
        'id' => 'cloudmazing-interactive-crm',
    ]);

    Event::fake();

    CallEvent::create([
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'call_type' => CallEvent::INCOMING_CALL,
        'to_number' => '+31850609000',
        'from_number' => '+31850609001',
        'tenant_id' => $tenant->id,
        'company_id' => $this->company->id,
        'event_time' => '2023-06-14 13:56:24',
    ]);

    $this->postJson('/webhooks/rinkel/call-insights?key=123456789', [
        'id' => '1c8b83a7c690084224fd3984515bc1a2',
        'sentiment' => 'POSITIVE',
        'summary' => 'This is a summary of the call.',
    ]);

    (new HandleCallInsightsListener())->handle(new CallInsightsEvent(
        new HandleCallInsightsDTO(
            id: '1c8b83a7c690084224fd3984515bc1a2',
            sentiment: 'POSITIVE',
            topics: [
                'sales',
                'support'
            ],
            summary: 'This is a summary of the call.',
        )
    ));

    $this->assertDatabaseMissing('call_events', [
        'call_id' => '1c8b83a7c690084224fd3984515bc1a2',
        'insights_summary' => 'This is a summary of the call.',
    ]);
});
