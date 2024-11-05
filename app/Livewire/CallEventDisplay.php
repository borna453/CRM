<?php

namespace App\Livewire;

use App\Models\CallEvent;
use Livewire\Component;

class CallEventDisplay extends Component
{
    public $callEventId;
    public $callEvent;

    public function mount($callEventId)
    {
        $this->callEvent = CallEvent::find($callEventId);
    }

    public function render()
    {
        return view('livewire.call-event-display');
    }
}
