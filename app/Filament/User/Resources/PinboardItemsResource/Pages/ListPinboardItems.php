<?php

namespace App\Filament\User\Resources\PinboardItemsResource\Pages;

use App\Filament\User\Resources\PinboardItemsResource;
use App\Models\PinboardItem;
use App\Traits\OpensModalOnRedirect;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPinboardItems extends ListRecords
{
    use AdvancedTables;
    use OpensModalOnRedirect;

    protected static string $resource = PinboardItemsResource::class;

    protected $listeners = ['refreshPinboardItems' => '$refresh'];

    public function mount(): void
    {
        parent::mount();

        $this->mountHandlesModalOpening();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->createAnother(false)->label(__('portal.pinboard_items.create')),
        ];
    }

    public function getPresetViews(): array
    {
        return [
            'open' => PresetView::make('Open')
                ->label(__('portal.open'))
                ->favorite()
                ->icon('heroicon-o-document')
                ->badge(PinboardItem::open()->whereVisibleTo(auth()->user())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->open()->orderBy('created_at', 'desc'))
                ->default(),
            'closed' => PresetView::make('Closed')
                ->label(__('portal.closed'))
                ->favorite()
                ->icon('heroicon-o-check')
                ->badge(PinboardItem::completed()->whereVisibleTo(auth()->user())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereVisibleTo(auth()->user())->completed()->orderBy('created_at', 'desc')),
            'deleted' => PresetView::make('Deleted')
                ->label(__('portal.deleted'))
                ->favorite()
                ->icon('heroicon-o-trash')
                ->badge(PinboardItem::onlyTrashed()->whereVisibleTo(auth()->user())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereVisibleTo(auth()->user())->onlyTrashed()->orderBy('deleted_at', 'desc')),
        ];
    }
}
