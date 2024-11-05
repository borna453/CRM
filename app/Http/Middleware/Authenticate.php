<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Filament\Http\Middleware\Authenticate as Middleware;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            return parent::handle($request, $next, ...$guards);
        } catch (HttpException $e) {
            /** @var User $user */
            $user = $request->user();

            if ($e->getStatusCode() !== 403 || ! $user) {
                throw $e;
            }

            return new RedirectResponse(match(true) {
                $user->isOwner() => '/owner',
                $user->isSuperAdmin() => '/superadmin',
                $user->isAdmin() => '/admin',
                $user->isUser() => '/user',
                default => '/login',
            });
        }
    }
}
