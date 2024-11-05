<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if (in_array($request->getHost(), config('tenancy.central_domains'))) {
        return redirect('/owner');
    }

    return match(true) {
        auth()->user()?->isSuperAdmin() => redirect('/admin'),
        auth()->user()?->isAdmin() => redirect('/admin'),
        auth()->user()?->isUser() => redirect('/user'),
        auth()->user()?->isEmployee() => redirect('/admin'),
        default => redirect('/login'),
    };
});

Route::middleware(\App\Http\Middleware\NotificationPreviewMiddleware::class)->get('/notifications/preview', \App\Http\Controllers\PreviewNotificationController::class)->name('notifications.preview');
