<?php

namespace App\Http;

use Filament\Facades\Filament;

class LoginResponse implements \Filament\Http\Responses\Auth\Contracts\LoginResponse
{
    public function toResponse($request)
    {
        if (tenant()) {
            return redirect()->to('/');
        }

        return redirect()->intended(Filament::getUrl());
    }
}
