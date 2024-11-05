<?php

namespace App\Livewire;

use App\Models\Recipient;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class RecipientList extends Component
{
    use WithPagination;

    public $messageId;
    public $userId = null;

    protected $listeners = ['open-modal' => 'setUserId', 'close-modal' => 'unsetUserId'];

    public function mount($messageId)
    {
        $this->messageId = $messageId;
    }

    public function openConversationModal($userId)
    {
        $this->userId = $userId;
        $this->dispatch('open-modal', id: 'conversation-modal');
    }

    #[On('close-modal')]
    public function unsetUserId()
    {
        $this->userId = null;
    }

    public function render()
    {
        $recipients = Recipient::where('message_id', $this->messageId)
            ->with('user')
            ->withCount('userReplies')
            ->orderBy('user_replies_count', 'desc')
            ->paginate(10);

        return view('livewire.recipient-list', ['recipients' => $recipients]);
    }
}
