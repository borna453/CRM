<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use App\Models\Conversation;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use phpDocumentor\Reflection\Types\False_;

class ListConversations extends ListRecords
{
    use AdvancedTables;

    protected static string $resource = ConversationResource::class;

    public function getPresetViews(): array
    {
        return [
            'open' => PresetView::make()
                ->label(__('portal.open'))
                ->badge(Conversation::whereHas('messages.tenant')->where('dt_is_completed', null)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('dt_is_completed', null)->orderBy('created_at', 'desc'))
                ->default()
                ->favorite()
                ->icon('heroicon-o-inbox'),
            'completed' => PresetView::make()
                ->label(__('portal.completed'))
                ->badge(Conversation::whereHas('messages.tenant')->where('dt_is_completed', '!=', null)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('dt_is_completed', '!=', null)->orderBy('dt_is_completed', 'desc'))
                ->favorite()
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
