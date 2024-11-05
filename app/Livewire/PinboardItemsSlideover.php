<?php

namespace App\Livewire;

use App\Models\PinboardItem;
use Livewire\Component;

class PinboardItemsSlideover extends Component
{
    public $userId;

    public function render()
    {
        $pinboardItems = PinboardItem::where('user_id', $this->userId)
            ->open()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.pinboard-items-slideover', ['pinboardItems' => $pinboardItems]);
    }
}
