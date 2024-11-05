<?php

namespace App\Filament\User\Resources\MessageResource\Pages;

use App\Filament\User\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Recipient;
use App\Traits\OpensModalOnRedirect;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class ListMessages extends ListRecords
{
    use OpensModalOnRedirect;
    use AdvancedTables;

    #[Url]
    public $model_id;

    protected static string $resource = MessageResource::class;

    public function mountHandlesModalOpening(): void
    {
        $this->model_id = request()?->get('model_id');

        if ($this->model_id) {
            $this->dispatch('openModal');
        }
    }

    #[On('openModal')]
    public function openModal()
    {
        $this->mountTableAction('view', $this->model_id);

        $this->model_id = null;
    }

    public function mount(): void
    {
        parent::mount();

        $this->mountHandlesModalOpening();
    }

    public function getPresetViews(): array
    {
        return [
            'open' => PresetView::make()
                ->label(__('portal.open'))
                ->badge(Recipient::where('user_id', auth()->id())->whereNull('seen_at')->count())
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('recipients', function (Builder $query) {
                        $query->where('user_id', auth()->id())
                            ->whereNull('seen_at');
                    });
                })
                ->default()
                ->favorite()
                ->icon('heroicon-o-inbox'),
            'seen' => PresetView::make()
                ->label(__('portal.messages.seen'))
                ->badge(Recipient::where('user_id', auth()->id())->whereNotNull('seen_at')->count())
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('recipients', function (Builder $query) {
                        $query->where('user_id', auth()->id())
                            ->whereNotNull('seen_at');
                    });
                })
                ->favorite()
                ->icon('heroicon-o-eye'),
            'conversations' => PresetView::make()
                ->label(__('portal.messages.conversations'))
                ->badge(Conversation::where('created_by', auth()->id())->count())
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('replies.conversation', function (Builder $query) {
                        $query->where('created_by', auth()->id());
                    });
                })
                ->favorite()
                ->icon('heroicon-o-chat-bubble-left-right'),
        ];
    }

    public function defaultPresetViewShouldBeApplied(): bool
    {
        return true;
    }
}
