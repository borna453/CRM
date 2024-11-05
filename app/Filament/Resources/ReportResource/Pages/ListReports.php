<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Enums\OnboardingTypes;
use App\Filament\Resources\ReportResource;
use App\Models\Report;
use App\Utils\Filament\Actions\OnboardingActionAttributeHelper;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReports extends ListRecords
{
    use AdvancedTables;

    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getPresetViews(): array
    {
        return [
            'all' => PresetView::make()
                ->label(__('portal.all'))
                ->badge(Report::count())
                ->modifyQueryUsing(fn (Builder $query) => $query->with(['appointment' => function ($query) {
                    $query->orderBy('dt_start', 'desc');
                }]))
                ->default()
                ->favorite()
                ->icon('heroicon-o-document-text'),
            'deleted' => PresetView::make()
                ->badge(Report::onlyTrashed()->count())
                ->label(__('portal.reports.deleted'))
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed()->orderBy('deleted_at', 'desc'))
                ->favorite()
                ->icon('heroicon-o-trash'),
        ];
    }
}
