<?php

namespace App\Observers;

use App\Models\Label;
use App\Models\Opportunity;
use App\Utils\Filament\FormFields\RichEditorAttachmentsHelper;
use Illuminate\Database\Eloquent\Model;

class OpportunityObserver
{
    public function creating(Opportunity $opportunity): void
    {
        $opportunity->user_id = auth()->user()->id;

        if($opportunity->text){
            $opportunity->text = RichEditorAttachmentsHelper::processContent($opportunity->text);
        }
    }

    public function saved(Opportunity $opportunity)
    {
        $opportunity->load('label');

        if ($opportunity->isDirty('label_id') && $opportunity?->label?->should_archive) {
            Model::withoutEvents(function () use ($opportunity){
                $opportunity->close();
            });
        }
    }
}
