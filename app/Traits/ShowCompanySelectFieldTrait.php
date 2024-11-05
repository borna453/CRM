<?php

namespace App\Traits;

use Livewire\Attributes\On;

trait ShowCompanySelectFieldTrait
{
    #[On('showCompanySelectField')]
    public function showSelect()
    {
        $this->mountedActionsData[0]['hideSelect'] = false;
        if(property_exists($this, 'data') && $this->data){
            $this->data['hideSelect'] = false;
        }
        if (property_exists($this, 'mountedFormComponentActionsData') && !empty($this->mountedFormComponentActionsData)) {
            $this->mountedFormComponentActionsData[0]['hideSelect'] = false;
        }

        if (property_exists($this, 'mountedTableActionsData') && $this->mountedTableActionsData) {
            $this->mountedTableActionsData[0]['hideSelect'] = false;
        }
        if(property_exists($this, 'editModalFormState') && $this->editModalFormState){
            $this->editModalFormState['hideSelect'] = false;
        }
    }

    #[On('showCompanyCard')]
    public function showCard()
    {
        $this->mountedActionsData[0]['showCard'] = true;
        if(property_exists($this, 'data') && $this->data){
            $this->data['showCard'] = true;
        }
        if(property_exists($this, 'mountedFormComponentActionsData') && $this->mountedFormComponentActionsData){
            $this->mountedFormComponentActionsData[0]['showCard'] = true;
        }
        if (property_exists($this, 'mountedTableActionsData') && $this->mountedTableActionsData) {
            $this->mountedTableActionsData[0]['showCard'] = true;
        }
        if(property_exists($this, 'editModalFormState') && $this->editModalFormState){
            $this->editModalFormState['showCard'] = true;
        }
    }

    #[On('hideCompanyCard')]
    public function hideCard()
    {
        $this->mountedActionsData[0]['showCard'] = false;
        if(property_exists($this, 'data') && $this->data){
            $this->data['hideSelectAfterCompanyCreate'] = false;
            $this->data['showCard'] = false;
            $this->data['hideSelect'] = false;
        }
        if(property_exists($this, 'mountedFormComponentActionsData') && $this->mountedFormComponentActionsData){
            $this->mountedFormComponentActionsData[0]['showCard'] = false;
        }
        if (property_exists($this, 'mountedTableActionsData') && $this->mountedTableActionsData) {
            $this->mountedTableActionsData[0]['showCard'] = false;
        }
        if(property_exists($this, 'editModalFormState') && $this->editModalFormState){
            $this->editModalFormState['showCard'] = false;
        }
    }
}
