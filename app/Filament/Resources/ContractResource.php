<?php

namespace App\Filament\Resources;

use App\Enums\ViewOptions;
use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Models\Company;
use App\Models\Contract;
use App\Utils\Filament\FormFields\ContractCosts;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema())
            ->statePath('data');
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }

    public static function getFormSchema($view = null): array
    {
        return [
            Forms\Components\Select::make('company_id')
                ->label(__('portal.companies.company'))
                ->options(Company::pluck('name', 'id'))
                ->required()
                ->createOptionForm(CompanyResource::getFormSchema())
                ->createOptionAction(function ($action) {
                    return $action->action(function ($data, $set) {
                        $company = Company::create($data);
                        $set('company_id', $company->id);
                    });
                })
                ->searchable(),
            Forms\Components\TextInput::make('value_per_month')
                ->label(__('portal.contracts.value_per_month'))
                ->required()
                ->numeric(),
            Repeater::make('costs')
                ->hiddenLabel()
                ->addActionLabel(__('portal.contracts.costs'))
                ->schema([
                Forms\Components\Grid::make()->schema([
                    TextInput::make('description')
                        ->columnSpan(8)
                        ->label(__('portal.description')),
                    TextInput::make('cost_estimate')
                        ->label(__('portal.opportunities.cost_estimate'))
                        ->numeric()
                        ->required()
                        ->prefix('€')
                        ->columnSpan(4),
                ])->columns(12)
            ])
            ->reorderable(false)
            ->collapsible()
            ->defaultItems(1),
        ];
    }
}
