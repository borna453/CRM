<?php

namespace App\Observers;

use App\Models\Report;
use App\Utils\Filament\FormFields\RichEditorAttachmentsHelper;
use Illuminate\Notifications\DatabaseNotification;
use Parallax\FilamentComments\Models\FilamentComment;

class ReportObserver
{
    public function creating(Report $report): void
    {
        if($report->description){
            $report->description = RichEditorAttachmentsHelper::processContent($report->description);
        }
    }

    public function deleted(Report $report): void
    {
        DatabaseNotification::where('data->viewData->model_type', Report::class)
            ->where('data->viewData->model_id', $report->id)
            ->delete();

        $comments = FilamentComment::where('subject_type', Report::class)
            ->where('subject_id', $report->id)
            ->get();

        foreach ($comments as $comment) {
            DatabaseNotification::where('data->viewData->model_type', FilamentComment::class)
                ->where('data->viewData->comment', $comment->id)
                ->delete();

            $comment->forceDelete();
        }
    }
}
