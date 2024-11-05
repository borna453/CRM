<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user && $user->locale && in_array($user->locale, config('app.supported_locale'))) {
            app()->setLocale($user->locale);
        }

        return $next($request);
    }
}
