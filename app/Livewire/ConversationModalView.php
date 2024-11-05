<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Utils\Filament\FormFields\MessageHelper;
use App\Utils\RichEditorButtons;
use Carbon\Carbon;
use Filament\Forms;
use Livewire\Component;
use Filament\Forms\Contracts\HasForms;

class ConversationModalView extends Component implements HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public $message;
    public $replies;
    public $replyContent;
    public $userId;

    public function mount($messageId, $userId = null): void
    {
        $this->userId = $userId;
        $this->message = Message::with('creator')->findOrFail($messageId);
        $this->message->formatted_created_at = Carbon::parse($this->message->created_at)->diffForHumans();

        $this->loadReplies();

        $this->markRepliesAsSeen();
    }

    public function loadReplies()
    {
        $conversation = Conversation::where('created_by', $this->userId)
            ->whereHas('messages', function ($query) {
                $query->where('parent_id', $this->message->id);
            })
            ->first();

        if ($conversation) {
            $this->replies = Message::where('conversation_id', $conversation->id)
                ->with('creator')
                ->get()
                ->map(function ($reply) {
                    $reply->formatted_created_at = Carbon::parse($reply->created_at)->diffForHumans();
                    return $reply;
                });
        } else {
            $this->replies = collect();
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\RichEditor::make('replyContent')
                ->label(__('portal.messages.your_reply'))
                ->maxWidth('full')
                ->toolbarButtons(RichEditorButtons::$toolbarButtons)
                ->placeholder(__('portal.messages.write_your_reply_here'))
                ->required(),
        ];
    }

    public function submitReply()
    {
        $this->validate();

        // Check if a conversation already exists for this user and message
        $conversation = Conversation::where('created_by', $this->userId)
            ->whereHas('messages', function ($query) {
                $query->where('parent_id', $this->message->id);
            })
            ->first();

        if (!$conversation) {
            // Create a new conversation for this user and message
            $conversation = Conversation::create([
                'parent_message_id' => $this->message->id,
                'created_by' => $this->userId,
            ]);
        }

        // Create the reply within the correct conversation
        Message::create([
            'conversation_id' => $conversation->id,
            'parent_id' => $this->message->id,
            'content' => $this->replyContent,
            'created_by' => auth()->id(),
        ]);

        $conversation->update(['dt_is_completed' => null]);

        $this->replyContent = '';

        $this->loadReplies();

        $this->dispatch('replyAdded');
    }

    public function markRepliesAsSeen()
    {
        $conversationId = optional($this->replies->first())->conversation_id;

        if (!$conversationId) {
            return;
        }

        if (auth()->user()->isUser()) {
            Message::where('conversation_id', $conversationId)
                ->whereNull('seen_at')
                ->whereHas('creator', function ($query) {
                    $query->whereHas('roles', function ($query) {
                        $query->where('name', User::ADMIN);
                    });
                })
                ->update(['seen_at' => now()]);
        } elseif (auth()->user()->isAdmin()) {
            Message::where('conversation_id', $conversationId)
                ->whereNull('seen_at')
                ->whereHas('creator', function ($query) {
                    $query->whereHas('roles', function ($query) {
                        $query->where('name', User::USER);
                    });
                })
                ->update(['seen_at' => now()]);
        }
    }

    public function getReplyAlignmentClass($reply)
    {
        $reply->load('creator');
        $isCurrentUser = $reply->created_by == auth()->id();
        $isCreatorAdmin = $reply->creator->isAdmin() || $reply->creator->isSuperAdmin() || $reply->creator->isOwner();

        return (auth()->user()->isUser() && $isCurrentUser) || (auth()->user()->isAdmin() && $isCreatorAdmin) ? 'justify-end' : 'justify-start';
    }

    public function getReplyBackgroundClass($reply)
    {
        return $this->getReplyAlignmentClass($reply) === 'justify-end' ? 'bg-blue-100 dark:bg-blue-800' : 'bg-gray-100 dark:bg-gray-800';
    }

    public function getReplyJustificationClass($reply)
    {
        return $this->getReplyAlignmentClass($reply) === 'justify-end' ? 'justify-end' : 'justify-start';
    }

    public function render()
    {
        return view('livewire.conversation-modal-view', [
            'originalMessage' => $this->message,
            'replies' => $this->replies,
        ]);
    }
}
