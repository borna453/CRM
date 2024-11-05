<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources;
use App\Filament\Owner\Resources\TenantResource\Pages;
use App\Filament\Owner\Resources\TenantResource\RelationManagers;
use App\Models\Tenant;
use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('company.name')
                    ->label(__('portal.tenant.company_name'))
                    ->live(debounce: 500)
                    ->afterStateUpdated(function (Forms\Set $set, $state){
                        $slug = Str::slug($state);

                        for ($i = 1; Tenant::where('id', $slug)->exists(); $i++) {
                            $slug = Str::slug($state) . '-' . $i;
                        }

                        $set('id', $slug);
                    })
                    ->required(),
                Forms\Components\TextInput::make('users.email')
                    ->label(__('portal.tenant.user_email'))
                    ->required(),
                Forms\Components\TextInput::make('id')
                    ->label(__('portal.tenant.domain_name'))
                    ->maxLength(255)
                    ->required()
                    ->unique('tenants', 'id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('encryption_key'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('tenant_login_page')
                    ->iconButton()
                    ->icon('heroicon-o-globe-alt')
                    ->iconSize('lg')
                    ->url(fn($record) => $record->route('login'))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('impersonate')
                    ->label(__('filament-impersonate::action.label'))
                    ->iconButton()
                    ->icon('impersonate-icon')
                    ->disabled(fn (Tenant $record) => ! $record->primaryUser || ! $record->primaryDomain)
                    ->form([
                        Forms\Components\Select::make('role')
                            ->label(__('portal.role'))
                            ->options([
                                User::USER => User::USER,
                                User::ADMIN => User::ADMIN,
                                User::SUPERADMIN => User::SUPERADMIN
                            ])
                            ->default(User::ADMIN)
                            ->required()
                    ])
                    ->action(function (Tenant $record, Tables\Actions\Action $action, array $data) {
                        $user = $record->users()->role($data['role'])->first();

                        if (! $user) {
                            $action->failureNotificationTitle('Not found');
                            $action->failure();
                            return;
                        }

                        self::impersonate($user, $action);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\DomainsRelationManager::class,
            RelationManagers\FeaturesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Resources\TenantResource\Pages\ListTenants::route('/'),
            'create' => Resources\TenantResource\Pages\CreateTenant::route('/create'),
            'view' => Resources\TenantResource\Pages\ViewTenant::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return Tenant::with(['primaryUser', 'primaryDomain']);
    }

    public static function impersonate(User $user, StaticAction $action): void
    {
        $tenant = $user->tenant;

        $appUrl = env('APP_URL');

        $scheme = str($appUrl)->before('://')->toString();
        $port = str($appUrl)->afterLast(':')->toString();
        $domain = $tenant->primaryDomain;

        $baseUrl = "$scheme://$domain->domain";

        if ($port && is_numeric($port)) {
            $baseUrl .= ":$port";
        }

        $token = tenancy()->impersonate($tenant, $user->id, $baseUrl.'/admin');

        $action->redirect("$baseUrl/impersonate/$token->token");
    }
}
