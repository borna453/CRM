<?php

namespace App\Livewire;

use App\Models\Message;
use App\Utils\Filament\FormFields\MessageHelper;
use Carbon\Carbon;
use Livewire\Component;

class MessageModalView extends Component
{
    public $message;

    public function mount($messageId)
    {
        $this->message = Message::where('id', $messageId)->first();
        $this->message->formatted_created_at = Carbon::parse($this->message->created_at)->diffForHumans();
    }

    public function render()
    {
        return view('livewire.message-modal-view');
    }
}
