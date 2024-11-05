<?php

namespace App\Filament\CustomActions;

use Filament\Tables\Actions\Action;
use STS\FilamentImpersonate\Concerns\Impersonates;

class ImpersonateGroupAction extends Action
{
    use Impersonates;
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('portal.impersonate'))
            ->icon('impersonate-icon')
            ->action(fn ($record) => $this->impersonate($record))
            ->hidden(fn ($record) => !$this->canBeImpersonated($record));
    }
}
