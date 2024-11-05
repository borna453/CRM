<?php

namespace App\Filament\Widgets;

use App\Enums\Permissions;
use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\OpportunityResource;
use App\Models\Opportunity;
use App\Utils\Filament\Actions\OpportunityActionHelper;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class OpportunityWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public ?array $data = [];

    public ?int $company_id = null;

    protected $listeners = ['refreshOpportunities' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(Opportunity::query()->with(['company', 'label'])->open())
            ->columns(OpportunityResource::getColumns())
            ->headerActions([
                Tables\Actions\Action::make('opportunity_list')
                    ->label(__('portal.view_all'))
                    ->outlined()
                    ->color('secondary')
                    ->visible(auth()->user()->hasTenantPermissionTo(Permissions::VIEW_OPPORTUNITIES->value))
                    ->url(OpportunityResource::getUrl('index')),
            ])
            ->actions([
                OpportunityActionHelper::closeOpportunity()->visible(fn() => !auth()->user()->hasTenantPermissionTo(Permissions::EDIT_OPPORTUNITIES->value)),
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->recordUrl(fn ($record) => ($record->company_id && auth()->user()->hasTenantPermissionTo(Permissions::EDIT_COMPANIES->value)) ? CompanyResource::getUrl('edit', ['record' => $record?->company_id]) : '')
            ->emptyStateHeading(__('portal.opportunities.empty'))
            ->emptyStateDescription(__('portal.opportunities.empty_description'))
            ->paginated(false);
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('portal.opportunities.open');
    }

    public static function canView(): bool
    {
        return auth()->user()->hasTenantPermissionTo(Permissions::VIEW_OPPORTUNITY_WIDGET->value);
    }
}
