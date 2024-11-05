<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\Permissions;
use App\Enums\ViewOptions;
use App\Filament\Resources\ReportResource;
use App\Models\Report;
use App\Models\User;
use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Components\PresetView;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReportsRelationManager extends RelationManager
{
    use AdvancedTables;

    protected static string $relationship = 'reports';

    public function form(Form $form): Form
    {
        return $form
            ->schema(ReportResource::getFormSchema(ViewOptions::USER_REPORTS, $this->ownerRecord));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                IconColumn::make('is_published')
                    ->label(__('portal.reports.publish'))
                    ->boolean()
                    ->alignCenter()
                    ->color('gray')
                    ->icon(function ($state) {
                        return $state ? 'heroicon-s-eye' : 'heroicon-s-eye-slash';
                    })
                    ->action(
                        Action::make('publish')
                            ->label(__('portal.reports.publish'))
                            ->requiresConfirmation(function ($record) {
                                return !$record->is_published;
                            })
                            ->action(function ($record) {
                                if (!$record->is_published){
                                    $record->publish();
                                }
                            })
                    ),
                TextColumn::make('title')
                    ->label(__('portal.reports.table_title'))
                    ->sortable(),
                TextColumn::make('appointment.dt_start')
                    ->label(__('portal.date'))
                    ->sortable()
                    ->date('d-m-Y'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->createAnother(false)
                    ->mutateFormDataUsing(function ($data){
                        $data['description'] = $data['fakedescription'];
                        unset($data['fakedescription']);
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->emptyStateHeading(__('portal.reports.no_reports'))
            ->emptyStateDescription(__('portal.reports.no_reports_description'))
            ->paginated(false);
    }

    public function getPresetViews(): array
    {
        return [
            'published' => PresetView::make()
                ->label(__('portal.reports.is_published'))
                ->badge(Report::published()->whereUserId($this->ownerRecord->id)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->published()->whereUserId($this->ownerRecord->id))
                ->default()
                ->favorite()
                ->icon('heroicon-o-check-circle'),
            'to_publish' => PresetView::make()
                ->label(__('portal.reports.to_publish'))
                ->badge(Report::toPublish()->whereUserId($this->ownerRecord->id)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->toPublish()->whereUserId($this->ownerRecord->id))
                ->favorite()
                ->icon('heroicon-o-clock'),
            'deleted' => PresetView::make()
                ->badge(Report::onlyTrashed()->whereUserId($this->ownerRecord->id)->count())
                ->label(__('portal.reports.deleted'))
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed()->whereUserId($this->ownerRecord->id))
                ->favorite()
                ->icon('heroicon-o-trash'),
        ];
    }

    public function defaultPresetViewShouldBeApplied(): bool
    {
        return true;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('portal.reports.reports');
    }

    protected static function getModelLabel(): ?string
    {
        return __('portal.reports.report');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('portal.reports.reports');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('viewAny', Report::class);
    }
}
