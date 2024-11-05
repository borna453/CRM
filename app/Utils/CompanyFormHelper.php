<?php

namespace App\Utils;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;

class CompanyFormHelper
{
    public static function formFields(): array
    {
        return [
            TextInput::make('name')
                ->label(__('portal.companies.name'))
                ->required(),

            TextInput::make('address')
                ->label(__('portal.companies.address')),


            Grid::make()
                ->schema([
                    TextInput::make('zip_code')
                        ->label(__('portal.companies.zip_code')),
                    TextInput::make('city')
                        ->label(__('portal.companies.city')),
                ]),

            TextInput::make('email')
                ->label(__('portal.email')),

            TextInput::make('phone_number')
                ->label(__('portal.companies.phone_number')),

            TextInput::make('coc_number')
                ->label(__('portal.companies.coc_number')),
        ];
    }
}
