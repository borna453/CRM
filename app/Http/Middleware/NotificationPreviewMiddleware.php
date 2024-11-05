<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NotificationPreviewMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !$user->hasAnyRole(['admin', 'owner'])) {
            abort(403);
        }

        return $next($request);
    }
}
