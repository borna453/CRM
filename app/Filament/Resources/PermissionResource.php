<?php

namespace App\Filament\Resources;

use App\Enums\Permissions;
use App\Filament\Resources\PermissionResource\Pages;
use App\Livewire\PermissionsGrid;
use App\Models\User;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-open';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Role::query()->where('name', User::USER)->orWhere('name', User::EMPLOYEE)

            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->badge()
                    ->label(__('portal.role'))
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_permissions')
                    ->label(__('portal.permissions.total'))
                    ->getStateUsing(function (Role $record) {
                        $permissionGroups = Permissions::groups();

                        if ($record->name === User::EMPLOYEE) {
                            $totalPermissions = collect($permissionGroups['Admin Resources'])
                                ->merge($permissionGroups['Admin Pages'])
                                ->merge($permissionGroups['Admin Widgets'])
                                ->flatten()
                                ->count();
                        } elseif ($record->name === User::USER) {
                            $totalPermissions = collect($permissionGroups['User Resources'])
                                ->merge($permissionGroups['User Pages'])
                                ->merge($permissionGroups['User Widgets'])
                                ->flatten()
                                ->count();
                        }

                        return $totalPermissions;
                    })
                    ->badge()
                    ->color('yellow'),

                Tables\Columns\TextColumn::make('assigned_permissions')
                    ->label(__('portal.permissions.assigned'))
                    ->getStateUsing(function (Role $record) {
                        return $record->permissions()->whereHas('roles', function (Builder $query) use ($record){
                            $query->where('role_id', $record->id)
                                ->where('role_has_permissions.tenant_id', tenant()->id);
                        })->count();
                    })->badge()->color('green'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->form(function ($record){
                    $roleName = $record->name;

                    $groupMapping = [
                        User::EMPLOYEE => [
                            'Resources' => Permissions::groups()['Admin Resources'],
                            'Pages' => Permissions::groups()['Admin Pages'],
                            'Widgets' => Permissions::groups()['Admin Widgets'],
                        ],
                        User::USER => [
                            'Resources' => Permissions::groups()['User Resources'],
                            'Pages' => Permissions::groups()['User Pages'],
                            'Widgets' => Permissions::groups()['User Widgets'],
                        ],
                    ];

                    $groups = $groupMapping[$roleName] ?? [];

                    return [
                        Tabs::make('Permissions')
                            ->tabs([
                                Tab::make('Resources')
                                    ->schema([
                                        Livewire::make(PermissionsGrid::class,[
                                            'roleName' => $roleName,
                                            'tenantId' => tenant()->id,
                                            'groups' => $groups['Resources'],
                                        ]),
                                    ]),
                                Tab::make('Pages')
                                    ->schema([
                                        Livewire::make(PermissionsGrid::class,[
                                            'roleName' => $roleName,
                                            'tenantId' => tenant()->id,
                                            'groups' => $groups['Pages'],
                                        ]),
                                    ]),
                                Tab::make('Widgets')
                                    ->schema([
                                        Livewire::make(PermissionsGrid::class,[
                                            'roleName' => $roleName,
                                            'tenantId' => tenant()->id,
                                            'groups' => $groups['Widgets'],
                                        ]),
                                    ]),
                            ]),
                    ];
                })->modalWidth('5xl')->modalFooterActions([]),
            ])
            ->recordAction(Tables\Actions\ViewAction::class);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('portal.permissions.permission');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.permissions.permissions');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->isAdmin();
    }
}
