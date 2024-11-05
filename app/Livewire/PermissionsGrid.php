<?php

namespace App\Livewire;

use Livewire\Component;

class PermissionsGrid extends Component
{
    public string $roleName;
    public string $tenantId;

    public array $groups = [];

    public function mount(string $roleName, string $tenantId, array $groups)
    {
        $this->roleName = $roleName;
        $this->tenantId = $tenantId;
        $this->groups = $groups;
    }

    public function render()
    {
        return view('livewire.permissions-grid', [
            'groups' => $this->groups,
        ]);
    }
}
