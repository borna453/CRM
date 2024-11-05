<?php

namespace App\Filament\Resources\OpportunityResource\Pages;

use App\Enums\OnboardingTypes;
use App\Filament\Resources\OpportunityResource;
use App\Models\Opportunity;
use App\Traits\ShowCompanySelectFieldTrait;
use App\Utils\Filament\Actions\OnboardingActionAttributeHelper;
use App\Utils\Filament\Actions\OpportunityActionHelper;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOpportunities extends ListRecords
{
    use ShowCompanySelectFieldTrait;
    use AdvancedTables;

    protected static string $resource = OpportunityResource::class;

    protected $listeners = ['refreshOpportunities' => '$refresh'];

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalHeading('')->action(fn($data) => OpportunityActionHelper::create($data)),
        ];
    }

    public function getPresetViews(): array
    {
        return [
            'open' => PresetView::make('Open')
                ->label(__('portal.open'))
                ->favorite()
                ->icon('heroicon-o-check')
                ->badge(Opportunity::whereNull('closed_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('closed_at')->orderBy('created_at', 'desc'))
                ->default(),
            'closed' => PresetView::make('Closed')
                ->label(__('portal.closed'))
                ->favorite()
                ->icon('heroicon-o-x-mark')
                ->badge(Opportunity::whereNotNull('closed_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('closed_at')->orderBy('closed_at', 'desc')),
        ];
    }
}
