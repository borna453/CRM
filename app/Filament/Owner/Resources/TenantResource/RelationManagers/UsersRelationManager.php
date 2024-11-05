<?php

namespace App\Filament\Owner\Resources\TenantResource\RelationManagers;

use App\Filament\Owner\Resources\TenantResource;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return UserResource::form($form);
    }

    public function table(Table $table): Table
    {
        return UserResource::table($table, true, true)
            ->actions([
                Tables\Actions\Action::make('impersonation')
                    ->label(__('filament-impersonate::action.label'))
                    ->iconButton()
                    ->icon('impersonate-icon')
                    ->disabled(fn (User $record) => ! $record->tenant->primaryDomain)
                    ->action(function (User $record, Tables\Actions\Action $action) {
                        TenantResource::impersonate($record, $action);
                    }),
            ]);
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        return User::query()->where('tenant_id', $this->ownerRecord->id)->with(['tenant']);
    }
}
