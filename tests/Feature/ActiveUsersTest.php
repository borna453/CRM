<?php

use App\Events\UserActivity;
use App\Http\Middleware\LastActivityMiddleware;
use App\Listeners\UserActivityListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

it('dispatches UserActivity event when middleware is triggered', function () {
    Event::fake();

    $user = $this->regularUser;
    Auth::login($user);

    $request = Request::create('/');
    $middleware = new LastActivityMiddleware();

    $middleware->handle($request, function () {});

    Event::assertDispatched(UserActivity::class, function ($event) use ($user) {
        return $event->user->id === $user->id;
    });
});

it('updates last_activity timestamp when UserActivity is handled', function () {
    $user = $this->regularUser;
    $listener = new UserActivityListener();

    UserActivity::dispatch($user);

    $listener->handle(new UserActivity($user));

    expect($user->fresh()->last_activity)->not()->toBeNull();
});
