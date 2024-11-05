<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Enums\Permissions;
use App\Enums\ViewOptions;
use App\Filament\Resources\OpportunityResource;
use App\Models\Note;
use App\Models\Opportunity;
use App\Models\Task;
use App\Utils\Filament\Actions\OpportunityActionHelper;
use App\Utils\RichEditorButtons;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OpportunitiesRelationManager extends RelationManager
{
    use AdvancedTables;
    protected static string $relationship = 'opportunities';

    public function form(Form $form): Form
    {
        $record = $form->getRecord();
        return $form
            ->schema(OpportunityResource::getFormSchema(ViewOptions::COMPANY_OPPORTUNITIES, $record?->id));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\Layout\Split::make([
                    TextColumn::make('title')
                        ->label(__('portal.opportunities.text'))
                        ->formatStateUsing(function ($record) {
                            return '
            <div>
                <div class="font-bold mb-1">' . e(Str::limit($record->title, 50)) . '</div>
                <div>' . Str::limit($record->text, 100) . '</div>
            </div>';
                        })
                        ->searchable()
                        ->sortable()
                        ->html(),
                    Tables\Columns\Layout\Stack::make([
                        TextColumn::make('expected_revenue')
                            ->label(__('portal.opportunities.expected_revenue'))
                            ->formatStateUsing(fn ($record) => $record->formatted_revenue)
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('label_id')
                            ->badge()
                            ->formatStateUsing(fn ($record) => $record->label->name)
                            ->label(__('portal.opportunities.label'))
                            ->color(fn($record)=> $record->label->color),
                        TextColumn::make('created_at')
                            ->label(__('portal.created_at'))
                            ->date('d-m-Y'),
                    ])
                ])
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->action(fn($data) => OpportunityActionHelper::create($data, $this->ownerRecord->id)),
            ])
            ->actions([
                OpportunityActionHelper::openOpportunity(),
                OpportunityActionHelper::closeOpportunity(),
                Tables\Actions\EditAction::make()->label('')->extraModalFooterActions([
                    DeleteAction::make()->successRedirectUrl(url()->previous())
                ]),
            ], position: Tables\Enums\ActionsPosition::BeforeColumns)
            ->modifyQueryUsing(fn(Builder $query) => $query->with('label'))
            ->emptyStateHeading(__('portal.opportunities.empty'))
            ->emptyStateDescription(__('portal.opportunities.empty_description'));
    }

    public function getPresetViews(): array
    {
        return [
            'open' => PresetView::make()
                ->label(__('portal.open'))
                ->favorite()
                ->default()
                ->icon('heroicon-o-check')
                ->badge($this?->getRelationship()->open()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->open()),
            'closed' => PresetView::make()
                ->label(__('portal.closed'))
                ->favorite()
                ->icon('heroicon-o-x-mark')
                ->badge($this?->getRelationship()->closed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->closed()),
        ];
    }

    protected static function getModelLabel(): ?string
    {
        return __('portal.opportunities.opportunity');
    }

    public static function getPluralLabel(): ?string
    {
        return __('portal.opportunities.opportunities');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('portal.opportunities.opportunities');
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('portal.opportunities.opportunities');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('viewAny', Opportunity::class);
    }
}
