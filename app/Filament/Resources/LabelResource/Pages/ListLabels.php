<?php

namespace App\Filament\Resources\LabelResource\Pages;

use App\Filament\Resources\LabelResource;
use App\Models\Label;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLabels extends ListRecords
{
    use AdvancedTables;

    protected static string $resource = LabelResource::class;


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading(function (){
                    return __('portal.labels.create_type', ['type' => $this->getActivePresetView()?->getLabel()]);
                })
                ->action(function ($data){
                    Label::create([
                        'name' => $data['name'],
                        'color' => $data['color'],
                        'type' => $this->activePresetView,
                        'finished_state' => $data['finished_state'] ?? false,
                        'show_on_board' => $data['show_on_board'] ?? false,
                        'should_archive' => $data['should_archive'] ?? false,
                    ]);
                }),
        ];
    }

    public function getPresetViews(): array
    {
        return [
            'opportunity' => PresetView::make()
                ->badge(Label::opportunityType()->count())
                ->label(__('portal.opportunities.opportunity'))
                ->modifyQueryUsing(fn (Builder $query) => $query->opportunityType())
                ->favorite()
                ->default()
                ->icon('heroicon-o-rectangle-stack'),
            'contract' => PresetView::make()
                ->label(__('portal.contracts.contract'))
                ->badge(Label::contractType()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->contractType())
                ->favorite()
                ->icon('heroicon-o-document-text'),
            'deals' => PresetView::make()
                ->label(__('portal.deals.deal'))
                ->badge(Label::dealType()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->dealType())
                ->favorite()
                ->icon('heroicon-o-currency-euro'),
        ];
    }

    public function defaultPresetViewShouldBeApplied(): bool
    {
        return true;
    }
}
