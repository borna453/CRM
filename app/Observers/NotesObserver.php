<?php

namespace App\Observers;

use App\Models\Note;
use App\Utils\Filament\FormFields\RichEditorAttachmentsHelper;

class NotesObserver
{
    public function creating(Note $note): void
    {
        $note->user_id = auth()->id();

        $note->note = RichEditorAttachmentsHelper::processContent($note->note);
    }
}
