<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Enums\Permissions;
use App\Filament\CustomActions\ImpersonateGroupAction;
use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Utils\Filament\Actions\InviteUserActionHelper;
use App\Utils\Filament\CompanyUsersStateFormatHelper;
use Closure;
use Filament\Forms\Components\Split;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn\IconColumnSize;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class CompanyUsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static string $view = 'filament.pages.company-users-relation-manager';

    protected $listeners = ['userInvited' => '$refresh'];

    public $openUserHistory = false;
    public $selectedRecord;

    public function form(Form $form): Form
    {
        return $form
            ->schema(UserResource::getFormSchema(User::COMPANY_USERS));
    }

    public function table(Table $table, bool $tenantManagement = false): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('name')
                            ->label(__('portal.name'))
                            ->searchable()
                            ->formatStateUsing(function ($record){
                                return new HtmlString('<span class="font-semibold">' . $record->name . '</span>');
                            })
                            ->icon(function ($record){
                                return $record && !$record->invited_at && $record->login_allowed && $record->email_enabled ? 'heroicon-o-exclamation-triangle' : null;
                            })
                            ->tooltip(function ($record){
                                return $record && !$record->invited_at && $record->login_allowed && $record->email_enabled ? __('portal.notifications.company.client_not_invited') : null;
                            })
                            ->iconColor('danger')
                            ->sortable(),
                        Tables\Columns\TextColumn::make('email')
                            ->label(__('portal.email'))
                            ->searchable()
                            ->sortable(),
                    ]),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('login_allowed')
                            ->label(__('portal.users.login_allowed'))
                            ->formatStateUsing(function ($state, $record) {
                                return CompanyUsersStateFormatHelper::formatBooleanState($record->login_allowed, __('portal.users.login_allowed'));
                            })

                            ->action(
                                Tables\Actions\Action::make('toggle-login-allowed')
                                    ->label(__('portal.users.login_allowed'))
                                    ->requiresConfirmation()
                                    ->modalHeading(function ($record){
                                        return $record->login_allowed ? __('portal.users.disable_login') : __('portal.users.enable_login');
                                    })
                                    ->modalDescription(function ($record){
                                        return $record->login_allowed ? __('portal.users.disable_login_confirmation') : __('portal.users.enable_login_confirmation');
                                    })
                                    ->action(function ($record){
                                        $record->update(['login_allowed' => !$record->login_allowed]);
                                    })
                            ),

                        Tables\Columns\TextColumn::make('email_enabled')
                            ->label(__('portal.users.email_enabled'))
                            ->formatStateUsing(function ($state, $record) {
                                return CompanyUsersStateFormatHelper::formatBooleanState($record->email_enabled, __('portal.users.email_enabled'));
                            })
                            ->action(
                                Tables\Actions\Action::make('toggle-email-enabled')
                                    ->label(__('portal.users.email_enabled'))
                                    ->requiresConfirmation()
                                    ->modalHeading(function ($record) {
                                        return $record->email_enabled ? __('portal.users.disable_email') : __('portal.users.enable_email');
                                    })
                                    ->modalDescription(function ($record) {
                                        return $record->email_enabled ? __('portal.users.disable_email_confirmation') : __('portal.users.enable_email_confirmation');
                                    })
                                    ->action(function ($record) {
                                        $record->update(['email_enabled' => !$record->email_enabled]);
                                    })
                            )
                    ])
                ])
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalWidth('5xl')
                    ->after(fn($record) => $record->assignRole(User::USER)),
            ])
            ->actions([
                Tables\Actions\EditAction::make('edit')
                    ->hiddenLabel()
                    ->icon('heroicon-o-pencil-square')
                    ->modalWidth('5xl'),
                Tables\Actions\ActionGroup::make([
                    InviteUserActionHelper::inviteAction()
                        ->color('primary'),
                    ImpersonateGroupAction::make()
                        ->color('primary')
                        ->redirectTo(function($record){
                            if($record->isAdmin()){
                                return '/admin';
                            }
                            return '/user';
                        })
                        ->visible(fn($record) => ! $tenantManagement && $record->login_allowed && !$record->deleted_at),
                    Tables\Actions\Action::make('view_history')
                        ->color('primary')
                        ->label(__('portal.appointments.history'))
                        ->icon('heroicon-o-clock')
                        ->visible(function ($record) {
                            return $record->appointments?->count() > 1;
                        })
                        ->action(fn($record) => $this->openUserHistory($record)),
                    Tables\Actions\DeleteAction::make(),
                ])->color('primary')
            ])
            ->bulkActions(UserResource::getBulkActions())
            ->modifyQueryUsing(function (Builder $query) {
                $query->with('appointments');
            })
            ->recordAction(null)
            ->emptyStateHeading(__('portal.users.no_contacts'))
            ->emptyStateDescription(__('portal.users.no_contacts_description'));
    }

    public function openUserHistory(User $record): void
    {
        $this->selectedRecord = $record;
        $this->openUserHistory = true;
    }

    public static function getModelLabel(): ?string
    {
        return __('portal.users.contact');
    }

    public static function getPluralLabel(): ?string
    {
        return __('portal.users.contacts');
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('portal.users.contacts');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('portal.users.contacts');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('viewAny', User::class);
    }
}
