<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')
                    ->label(__('portal.profile.info.first_name'))
                    ->reactive()
                    ->live(debounce: 500)
                    ->formatStateUsing(function ($state){
                        return ucwords($state);
                    })
                    ->afterStateUpdated(function ($state, Set $set){
                        $set('first_name', ucwords($state));
                    })
                    ->required(),
                TextInput::make('last_name')
                    ->label(__('portal.profile.info.last_name'))
                    ->reactive()
                    ->live(debounce: 500)
                    ->formatStateUsing(function ($state){
                        return ucwords($state);
                    })
                    ->afterStateUpdated(function ($state, Set $set){
                        $set('last_name', ucwords($state));
                    })
                    ->required(),
                TextInput::make('email')
                    ->label(__('portal.email'))
                    ->email()
                    ->required(),
                Select::make('locale')
                    ->label(__('portal.users.locale'))
                    ->options([
                        'en' => __('portal.locales.en'),
                        'nl' => __('portal.locales.nl'),
                    ])
                    ->default('nl')
                    ->required(),
                TextInput::make('password')
                    ->label(__('portal.password'))
                    ->password()
                    ->revealable()
                    ->required(),
                TextInput::make('password_confirmation')
                    ->label(__('portal.confirm_password'))
                    ->password()
                    ->revealable()
                    ->dehydrated(false)
                    ->same('password')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordAction(Tables\Actions\EditAction::class);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role(User::OWNER);
    }
}
