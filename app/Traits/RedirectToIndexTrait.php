<?php

namespace App\Traits;

trait RedirectToIndexTrait
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
