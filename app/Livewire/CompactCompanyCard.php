<?php

namespace App\Livewire;

use App\Models\Company;
use Livewire\Component;

class CompactCompanyCard extends Component
{
    public ?Company $company = null;
    public $companyId;

    protected $listeners = ['companyIdUpdated'];

    public function mount($companyId)
    {
        $this->companyId = $companyId;
        $this->loadCompany();
    }

    public function companyIdUpdated($companyId)
    {
        $this->companyId = $companyId;
        $this->loadCompany();
    }

    protected function loadCompany()
    {
        $this->company = Company::find($this->companyId);
    }

    public function hideCard()
    {
        $this->dispatch('showCompanySelectField');
        $this->dispatch('hideCompanyCard');

        $this->company = null;
        $this->companyId = null;
    }

    public function render()
    {
        return view('livewire.compact-company-card', ['company' => $this->company]);
    }
}

