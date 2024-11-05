<?php

namespace App\Livewire\Appointments;

use Livewire\Component;

class DetailsComponent extends Component
{
    public $appointment;

    public function render()
    {
        return view('livewire.details-component');
    }
}
