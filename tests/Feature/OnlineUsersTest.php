<?php

use App\Events\UserActivity;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Http\Middleware\LastActivityMiddleware;
use App\Listeners\UserActivityListener;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use function Pest\Livewire\livewire;

it('dispatches UserActivity event when authenticated user is not impersonated', function () {
    Event::fake();

    $this->actingAs($this->adminUser);

    $request = Request::create('http://cloudmazing.cloudmazing-interactive-crm.test/admin');
    $middleware = new LastActivityMiddleware();
    $middleware->handle($request, fn() => response());

    Event::assertDispatched(UserActivity::class, function ($event)  {
        return $event->user->id === $this->adminUser->id;
    });
});

it('does not dispatch UserActivity event when user is impersonated', function () {
    Event::fake();

    $this->actingAs($this->adminUser);

    livewire(ListUsers::class)
        ->callTableAction('impersonate', $this->regularUser->id);

    $request = Request::create('http://cloudmazing.cloudmazing-interactive-crm.test/admin');
    $middleware = new LastActivityMiddleware();
    $middleware->handle($request, fn() => response());

    Event::assertNotDispatched(UserActivity::class);
});

it('updates user last_activity timestamp', function () {
    Queue::fake();

    $user = User::factory()->create([
        'last_activity' => null,
    ]);

    $event = new UserActivity($user);
    $listener = new UserActivityListener();
    $listener->handle($event);

    $user->refresh();
    expect($user->last_activity)->not->toBeNull();
});
