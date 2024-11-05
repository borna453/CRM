<?php

namespace App\Filament\Widgets;

use App\Enums\Permissions;
use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class CompanyOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected $listeners = [
        'refreshCompanyOverviewWidget' => '$refresh',
        'refreshTasks' => '$refresh',
        'refreshOpportunities' => '$refresh',
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Company::query()
                    ->withoutAdminCompany()
                    ->withCount(['tasks as tasks_count' => fn($query) => $query->open()])
                    ->withCount(['opportunities as opportunities_count' => fn($query) => $query->open()])
                    ->orderBy('opportunities_count', 'desc')
                    ->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('portal.companies.name')),
                Tables\Columns\TextColumn::make('tasks_count')
                    ->label(__('portal.todo'))
                    ->alignCenter()
                    ->formatStateUsing(fn($record) => $record->tasks_count),
                Tables\Columns\TextColumn::make('opportunities_count')
                    ->label(__('portal.opportunities.opportunities'))
                    ->alignCenter()
                    ->formatStateUsing(fn($record) => $record->opportunities_count),
            ])
            ->headerActions([
                Tables\Actions\Action::make('view_all')
                    ->outlined()
                    ->color('secondary')
                    ->label(__('portal.view_all'))
                    ->url(CompanyResource::getUrl('index'))
            ])
            ->recordUrl(fn ($record) => auth()->user()->hasTenantPermissionTo(Permissions::EDIT_COMPANIES->value) ? CompanyResource::getUrl('edit', ['record' => $record->id]) : '')
            ->paginated(5)
            ->paginationPageOptions(['5'])
            ->emptyStateHeading(__('portal.companies.no_companies'));
    }

    public function getColumnSpan(): int|string|array
    {
        return [
            'default' => 2,
            'sm' => 2,
            'md' => 2,
            'lg' => 2,
            'xl' => 2,
            '2xl' => 1,
        ];
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('portal.companies.overview');
    }

    public static function canView(): bool
    {
        return auth()->user()->hasTenantPermissionTo(Permissions::VIEW_COMPANY_OVERVIEW_WIDGET->value);
    }
}
