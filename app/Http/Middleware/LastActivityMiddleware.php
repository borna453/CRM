<?php

namespace App\Http\Middleware;

use App\Events\UserActivity;
use Closure;
use Illuminate\Http\Request;

class LastActivityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if(auth()->user() && ! app('impersonate')->isImpersonating()){
            UserActivity::dispatch(auth()->user());
        }
        return $next($request);
    }
}
