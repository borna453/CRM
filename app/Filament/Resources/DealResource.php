<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DealResource\Pages;
use App\Filament\Resources\DealResource\RelationManagers;
use App\Models\Company;
use App\Models\Deal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DealResource extends Resource
{
    protected static ?string $model = Deal::class;

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeals::route('/'),
            'create' => Pages\CreateDeal::route('/create'),
            'edit' => Pages\EditDeal::route('/{record}/edit'),
        ];
    }

    public static function getFormSchema($view = 'default'): array
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
            Forms\Components\TextInput::make('total_value')
                ->label(__('portal.deals.total_value'))
                ->required()
                ->numeric(),
        ];
    }
}
