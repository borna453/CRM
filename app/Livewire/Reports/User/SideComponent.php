<?php

namespace App\Livewire\Reports\User;

use App\Models\Report;
use App\Models\User;
use Livewire\Component;

class SideComponent extends Component
{
    public $reports;

    public function mount()
    {
        $this->reports = Report::whereVisibleTo()->published()->with(User::USER)
            ->whereHas('appointment', function ($query) {
            $query->orderBy('dt_start', 'desc');
        })->get();
    }

    public function render()
    {
        return view('livewire.reports.user.side-component', [
            'reports' => $this->reports
        ]);
    }
}
