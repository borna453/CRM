<?php

namespace App\Utils\Filament\Actions;

use App\Enums\Features;
use App\Enums\LocationOptions;
use App\Filament\Resources\OpportunityResource;
use App\Livewire\UpcomingAppointmentsCalendar;
use App\Models\Appointment;
use App\Models\Feature;
use App\Models\Opportunity;
use App\Models\PinboardItem;
use App\Models\Task;
use App\Models\User;
use App\Traits\DateMutateFieldsTrait;
use App\Utils\AppointmentValidatorHelper;
use App\Utils\Filament\FormFields\AppointmentUserIdSelectHelper;
use App\Utils\RichEditorButtons;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;

class QuickActionsHelper
{
    use DateMutateFieldsTrait;

    public static function appointmentAction(): Action
    {
        return Action::make('createAppointment')
            ->label(__('portal.appointments.create'))
            ->visible(fn() => Feature::isActive(Features::APPOINTMENTS_AND_REPORTS) && auth()->user()->isAdmin())
            ->form(self::appointmentModalForm())
            ->modalWidth('4xl')
            ->mutateFormDataUsing(function ($data): array {
                return self::combineDateFields($data);
            })
            ->action(function ($data, $livewire) {
                Appointment::create($data);
                $livewire->dispatch('reload')->to(UpcomingAppointmentsCalendar::class);
            });
    }

    public static function appointmentModalForm(): array
    {
        return [
            AppointmentUserIdSelectHelper::userSelectField()->model(Appointment::class),
            TextInput::make('title')
                ->label(__('portal.reports.table_title'))
                ->autofocus()
                ->required()
                ->columnSpanFull(),
            Select::make('location')
                ->label(__('portal.location'))
                ->options(LocationOptions::options())
                ->default(LocationOptions::MY_LOCATION->value)
                ->reactive()
                ->required(),
            TextInput::make('online_url')
                ->label(__('portal.appointments.online_url'))
                ->hidden(function(Get $get) {
                    return $get('location') !== LocationOptions::ONLINE->value;
                }),
            TextInput::make('other_location')
                ->label(__('portal.appointments.other_location'))
                ->hint(__('portal.appointments.other_location_hint'))
                ->hidden(function(Get $get) {
                    return $get('location') !== LocationOptions::OTHER->value;
                }),
            DatePicker::make('date')
                ->label(__('portal.date'))
                ->columnSpanFull()
                ->date('d-m-Y')
                ->native(false)
                ->required(),
            TimePicker::make('start_time')
                ->label(__('portal.start_time'))
                ->time()
                ->columnSpanFull()
                ->seconds(false)
                ->required(),
            TimePicker::make('end_time')
                ->label(__('portal.end_time'))
                ->time()
                ->rules([
                    fn(Get $get): Closure => function ($value, $attribute, Closure $fail) use ($get) {
                        AppointmentValidatorHelper::validateOverlappingTimes($value, $attribute, $fail, $get);
                    }
                ])
                ->after('start_time')
                ->validationMessages([
                    'after' => __('portal.appointments.end_time_after_start_time')
                ])
                ->columnSpanFull()
                ->seconds(false)
                ->required(),
            RichEditor::make('description')
                ->label(__('portal.description'))
                ->columnStart(1)
                ->columnSpanFull()
                ->toolbarButtons(RichEditorButtons::$toolbarButtons),
        ];
    }

    public static function taskAction(): Action
    {
        return Action::make('create_task')
            ->label(__('portal.tasks.create'))
            ->form(self::taskActionModalForm())
            ->action(function ($data, $livewire) {
                $livewire->dispatch('refreshTasks');
                return Task::create($data);
            })
            ->mutateFormDataUsing(function (array $data): array {
                if (auth()->user()->isUser()) {
                    $data['user_id'] = auth()->id();
                }
                return $data;
            });
    }

    public static function taskActionModalForm($view = null): array
    {
        return [
            TextInput::make('title')
                ->label(__('portal.tasks.title'))
                ->required(empty($view))
                ->requiredWith('information,user_id,dt_complete_by')
                ->autofocus()
                ->reactive()
                ->columnSpanFull(),
            Textarea::make('information')
                ->rows(4)
                ->reactive()
                ->label(__('portal.tasks.information'))
                ->columnSpanFull(),
            Select::make('user_id')
                ->label(__('portal.users.user'))
                ->options(User::assignableUsers()->pluck('name', 'id'))
                ->searchable()
                ->reactive()
                ->visible(fn() => auth()->user()->isAdmin())
                ->columnSpanFull(),
            DatePicker::make('dt_complete_by')
                ->label(__('portal.complete_by'))
                ->reactive()
                ->columnSpanFull(),
            Toggle::make('is_private')
                ->label(__('portal.tasks.is_private'))
                ->reactive()
                ->visible(fn() => auth()->user()->isUser())
                ->columnSpanFull(),
        ];
    }

    public static function opportunityAction(): Action
    {
        return Action::make('create_opportunity')
            ->label(__('portal.opportunities.create'))
            ->visible(fn() => auth()->user()->isAdmin())
            ->form(OpportunityResource::getFormSchema())
            ->record(new Opportunity())
            ->action(function ($data) {
                OpportunityActionHelper::create($data);
            });
    }

    public static function companyAction(): Action
    {
        return Action::make('create_company')
            ->label(__('portal.companies.create'))
            ->visible(fn() => auth()->user()->isAdmin())
            ->url(route('filament.admin.resources.companies.create'));
    }

    public static function pinboardAction(): Action
    {
        return Action::make('create_pinboard_item')
            ->label(__('portal.pinboard_items.create'))
            ->visible(fn() => auth()->user()->isUser())
            ->form([
                TextInput::make('title')
                    ->label(__('portal.table_title'))
                    ->autofocus()
                    ->required(),
                RichEditor::make('description')
                    ->label(__('portal.description'))
                    ->toolbarButtons(RichEditorButtons::$toolbarButtons)
                    ->columnSpanFull()
                    ->required(),
            ])
            ->action(function ($data, $livewire) {
                $livewire->dispatch('refreshPinboardItemsWidget');
                return PinboardItem::create($data);
            });
    }
}
