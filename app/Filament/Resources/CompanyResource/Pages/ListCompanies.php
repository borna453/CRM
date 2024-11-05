<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanies extends ListRecords
{
    use AdvancedTables;

    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getPresetViews(): array
    {
        return [
            'active' => PresetView::make('Active')
                ->label(__('portal.active'))
                ->favorite()
                ->icon('heroicon-o-check')
                ->badge(Company::query()->count())
                ->default(),
            'archived' => PresetView::make('Archived')
                ->label(__('portal.archive'))
                ->favorite()
                ->icon('heroicon-o-archive-box')
                ->badge(Company::onlyTrashed()->count())
                ->modifyQueryUsing(fn ($query) => $query->onlyTrashed()->orderBy('deleted_at', 'desc')),
        ];
    }
}
