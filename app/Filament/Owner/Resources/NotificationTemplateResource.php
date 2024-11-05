<?php

namespace App\Filament\Owner\Resources;

use App\Enums\NotificationTypeEnum;
use App\Enums\Permissions;
use App\Filament\Owner\Resources\NotificationTemplateResource\Pages;
use App\Mail\WeeklyAppointmentReminderMail;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Notifications\TasksReminder;
use App\Utils\Notifications\NotificationHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Request;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        $record = $form->getRecord();
        return $form
            ->schema(fn() => [
                self::params(),
                Forms\Components\Section::make()
                    ->heading(__('portal.notifications.types.email'))
                    ->schema([
                        Forms\Components\RichEditor::make('email_content')
                            ->label(__('portal.notifications.types.email_content'))
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->reactive()
                            ->formatStateUsing(function ($state){
                                return htmlspecialchars_decode($state);
                            })
                            ->live(debounce: 250)
                            ->hint(__('portal.notifications.types.preview_hint'))
                            ->disableToolbarButtons(['attachFiles']),
                    ]),
                Forms\Components\TextInput::make('email_subject')
                    ->label(__('portal.notifications.types.email_subject'))
                    ->reactive()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('button_text')
                    ->label(__('portal.notifications.types.button_text'))
                    ->reactive()
                    ->columnSpanFull()
                    ->visible(fn() => !is_null($record->button_text))->hint(function ($get) use ($record){
                    if(empty($get('button_text'))){
                        return __('portal.notifications.types.button_text_hint') . $record->button_text;
                    }
                }),
                Forms\Components\Section::make()->heading(__('portal.notifications.types.preview'))
                    ->icon('heroicon-o-eye')
                    ->reactive()
                    ->schema(function (Forms\Get $get){
                        $type = NotificationTypeEnum::from($get('type'));

                        $routeData = [
                            'type' => $type->value,
                            'email_content' => $get('email_content'),
                            'button_text' => $get('button_text'),
                        ];

                        $url = tenant()
                            ? tenant()?->route('notifications.preview', $routeData)
                            : route('notifications.preview', $routeData);

                        [$type, $emailContent, $params] = NotificationHelper::processNotificationContent($type, $get('email_content'), $get('button_text'));


                        return [
                            Forms\Components\ViewField::make('preview')
                                ->dehydrated(false)
                                ->hiddenLabel()
                                ->view('filament.notification-preview')
                                ->viewData(['url' => $url, 'type' => $type, 'email_content' => $emailContent]),
                        ];
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->formatStateUsing(function ($record){
                    return NotificationTypeEnum::from($record->type)->label();
                })->label(__('portal.notifications.types.type')),
                Tables\Columns\TextColumn::make('description')->getStateUsing(function ($record){
                    return NotificationTypeEnum::from($record->type)->description();
                })->label(__('portal.description')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
            ])
            ->paginated(false)
            ->modifyQueryUsing(function (Builder $query){
                $query = $query->withoutTenancy()->whereNull('tenant_id');
                if (filament()->getCurrentPanel()?->getId() === User::ADMIN){
                    $query = $query->adminNotifications();
                }
                return $query;
            })
            ->recordUrl(function ($record){
                if(filament()->getCurrentPanel()?->getId() === User::ADMIN && !auth()->user()->can('update', NotificationTemplate::class)){
                    return null;
                }
                if(filament()->getCurrentPanel()?->getId() === User::ADMIN && NotificationTemplate::where('type', $record->type)->where('tenant_id', tenant()?->id)->exists()){
                    return NotificationTemplateResource::getUrl('edit', ['record' => NotificationTemplate::where('type', $record->type)->where('tenant_id', tenant()?->id)->first()]);
                }

                return NotificationTemplateResource::getUrl('edit', ['record' => $record]);
            });
    }

    public static function params(?NotificationTypeEnum $type = null): Forms\Components\Section
    {
        return Forms\Components\Section::make()
            ->columnSpanFull()
            ->collapsible()
            ->heading(__('portal.notifications.types.parameters'))
            ->extraAttributes(['class' => 'fi-section-compact'])
            ->visible(fn(Forms\Get $get) => $type !== null || $get('type') !== null)
            ->schema(function (Forms\Get $get) use ($type){
                if (! $type) {
                    $type = NotificationTypeEnum::from($get('type'));
                }

                $notificationClass = $type->notificationClass();
                $params = call_user_func([$notificationClass, 'templateParamsList']);

                return [
                    Forms\Components\View::make('available_params_table')
                        ->view('filament.notification-params', compact('type', 'params')),
                ];
            });
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'type';
    }

    public static function resolveRecordRouteBinding(int|string $key): ?Model
    {
        $tenantTemplate = parent::resolveRecordRouteBinding($key);

        if($tenantTemplate){
            return $tenantTemplate;
        }

        return NotificationTemplate::query()->withoutTenancy()->whereNull('tenant_id')->where('type', $key)->first();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationTemplates::route('/'),
            'edit' => Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('portal.notifications.types.template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portal.notifications.types.templates');
    }

    public static function canViewAny(): bool
    {
        if(filament()->getCurrentPanel()?->getId() === User::ADMIN){
            return auth()->user()->can('viewAny', NotificationTemplate::class);
        }

        return true;
    }

    public static function canEdit(Model $record): bool
    {
        if(filament()->getCurrentPanel()?->getId() === User::ADMIN){
            return auth()->user()->can('update', NotificationTemplate::class);
        }

        return true;
    }
}
