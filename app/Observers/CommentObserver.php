<?php

namespace App\Observers;

use App\Models\Report;
use App\Models\User;
use App\Notifications\CommentCreated;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Illuminate\Notifications\DatabaseNotification;
use Parallax\FilamentComments\Models\FilamentComment;

class CommentObserver
{
    public function created(FilamentComment $comment): void
    {
        $report = Report::find($comment->subject_id);

        $user = $comment->user;

        if($user->hasRole('admin')){
            $userNotification = new CommentCreated($comment, $report->user);

            $report->user?->notify($userNotification);
            event(new DatabaseNotificationsSent($report->user));
        }

        if($user->hasRole('user')){
            $adminUsers = User::role(User::ADMIN)->get();
            
            foreach($adminUsers as $adminUser){
                $adminNotification = new CommentCreated($comment, $adminUser);

                $adminUser->notify($adminNotification);
                event(new DatabaseNotificationsSent($adminUser));
            }
        }
    }

    public function deleted(FilamentComment $comment): void
    {
        DatabaseNotification::where('data->viewData->model_type', FilamentComment::class)
            ->where('data->viewData->model_id', $comment->id)
            ->delete();
    }
}
