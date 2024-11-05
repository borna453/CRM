<?php

namespace App\Utils\Filament\FormFields;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class FinancialGoalFormHelper
{
    public static function fields(): array
    {
        return [
            Placeholder::make('year')
                ->label(__('portal.year'))
                ->content(date('Y'))
                ->hiddenLabel(),
            TextInput::make('goal')
                ->required()
                ->label(__('portal.financial_report.goal'))
                ->prefix('€')
                ->numeric()
                ->minValue(0),
            TextInput::make('achieved')
                ->label( __('portal.financial_report.achieved'))
                ->prefix('€')
                ->numeric()
                ->minValue(0),
        ];
    }
}
