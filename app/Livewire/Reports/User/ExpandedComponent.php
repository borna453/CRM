<?php

namespace App\Livewire\Reports\User;

use App\Models\Report;
use Livewire\Component;

class ExpandedComponent extends Component
{
    public $reportId;
    public $report;

    public function mount()
    {
        $this->report = Report::find($this->reportId);
    }

    public function render()
    {
        return view('livewire.reports.user.expanded-component', [
            'report' => $this->report,
        ]);
    }
}
