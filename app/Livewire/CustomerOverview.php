<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class CustomerOverview extends Component
{
    public $userId;

    public function render()
    {
        $user = User::find($this->userId);

        return view('livewire.customer-overview', ['currentUserId' => $this->userId, 'user' => $user]);
    }
}
