<?php

namespace App\Filament\Resources\AppointmentResource\Widgets;

use App\Enums\LocationOptions;
use App\Enums\Permissions;
use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\UserResource;
use App\Models\Appointment;
use App\Models\Company;
use App\Models\User;
use App\Traits\DateMutateFieldsTrait;
use App\Utils\AppointmentValidatorHelper;
use App\Utils\Filament\FormFields\AppointmentUserIdSelectHelper;
use App\Utils\RichEditorButtons;
use Carbon\Carbon;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Livewire\Attributes\On;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    use DateMutateFieldsTrait;

    public Model|string|null $model = Appointment::class;

    public $initialView;

    public $initialDate;

    public bool $isOnboarding = false;

    public function config(): array
    {
        return [
            'initialView' => $this->initialView,
            'initialDate' => $this->initialDate,
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'timeGridWeek,timeGridDay,dayGridMonth',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
            'allDaySlot' => false,
            'selectable' => true,
            'editable' => true,
            'eventTimeFormat' => [
                'hour' => '2-digit',
                'minute' => '2-digit',
                'meridiem' => false,
            ],
        ];
    }


    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        $appointment = Appointment::find($event['id']);

        if ($appointment) {
            $adminTimezone = auth()->user()->timezone;

            $startUtc = Carbon::parse($event['start'], 'UTC');
            $endUtc = Carbon::parse($event['end'], 'UTC');

            $startAdminTimezone = Carbon::createFromFormat('Y-m-d H:i:s', $startUtc, $adminTimezone)?->setTimezone($adminTimezone);
            $endAdminTimezone = Carbon::createFromFormat('Y-m-d H:i:s', $endUtc, $adminTimezone)?->setTimezone($adminTimezone);

            $startUtc = $startAdminTimezone->copy()->setTimezone('UTC');
            $endUtc = $endAdminTimezone->copy()->setTimezone('UTC');

            $appointment->dt_start = $startUtc->format('Y-m-d H:i:s');
            $appointment->dt_end = $endUtc->format('Y-m-d H:i:s');
            $appointment->save();

            return false;
        }

        return true;
    }

    public function getFormSchema(): array
    {
        return[
            AppointmentUserIdSelectHelper::userSelectField(),
            TextInput::make('title')
                ->label(__('portal.reports.table_title'))
                ->autofocus()
                ->required(),
            Select::make('location')
                ->label(__('portal.location'))
                ->options(LocationOptions::options())
                ->default(LocationOptions::MY_LOCATION->value)
                ->reactive()
                ->required(),
            TextInput::make('online_url')
                ->label(__('portal.appointments.online_url'))
                ->hidden(function(Get $get){
                    return $get('location') !== LocationOptions::ONLINE->value;
                }),
            TextInput::make('other_location')
                ->label(__('portal.appointments.other_location'))
                ->hint(__('portal.appointments.other_location_hint'))
                ->hidden(function(Get $get){
                    return $get('location') !== LocationOptions::OTHER->value;
                }),
            DatePicker::make('date')
                ->label(__('portal.date'))
                ->required()
                ->date('d-m-Y')
                ->native(false)
                ->minDate(now()->toDateString()),
            TimePicker::make('start_time')
                ->label(__('portal.start_time'))
                ->time()
                ->seconds(false)
                ->required(),
            TimePicker::make('end_time')
                ->label(__('portal.end_time'))
                ->required()
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
                ->seconds(false),
            RichEditor::make('description')
                ->label(__('portal.description'))
                ->toolbarButtons(RichEditorButtons::$toolbarButtons)
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Appointment::query()
            ->with('user')
            ->where('dt_start', '>=', $fetchInfo['start'])
            ->where('dt_end', '<=', $fetchInfo['end'])
            ->get()
            ->map(function (Appointment $event) {
                $hasEditPermission = auth()->user()->can('update', Appointment::class);

                $eventArray = [
                    'id' => $event->id,
                    'title' => $event->user->name,
                    'appointmentTitle' => $event->title,
                    'start' => $event->dt_start->setTimezone(auth()->user()->timezone)->format('Y-m-d\TH:i:s'),
                    'end' => $event->dt_end->setTimezone(auth()->user()->timezone)->format('Y-m-d\TH:i:s'),
                    'shouldOpenUrlInNewTab' => false,
                ];

                if ($hasEditPermission) {
                    $eventArray['url'] = AppointmentResource::getUrl('view', ['record' => $event]);
                }

                return $eventArray;
            })
            ->all();
    }
    protected function headerActions(): array
    {
        return [
            Action::make('view')
                ->label(__('portal.appointments.list'))
                ->url(fn (): string => AppointmentResource::getUrl('list')),
            CreateAction::make()
                ->label(__('portal.appointments.create'))
                ->modalHeading(__('portal.appointments.create'))
                ->mountUsing(function (Form $form, array $arguments){
                    $fillData = [
                        'location' => LocationOptions::MY_LOCATION->value,
                    ];

                    if (!empty($arguments)) {
                        $start = $arguments['start'];
                        $end = $arguments['end'];

                        $fillData['date'] = $start ? $start->format('d-m-Y') : null;

                        if (!$arguments['allDay']) {
                            $fillData['start_time'] = $start ? $start->format('H:i') : null;
                            $fillData['end_time'] = $end ? $end->format('H:i') : null;
                        }
                    }

                    $form->fill($fillData);
                })
                ->mutateFormDataUsing(function (array $data): array {
                    return $this->combineDateFields($data);
                })
                ->createAnother(false)
                ->extraAttributes(fn() => [
                    'class' => $this->isOnboarding ? 'glowing-light' : '',
                ])
        ];
    }

    protected function modalActions(): array
    {
        return [
            EditAction::make()
                ->disabled(fn() => auth()->user()->can('update', Appointment::class))
                ->mountUsing(function (Appointment $record, Form $form, array $arguments){
                    $form->fill([
                        'user_id' => $record->user_id,
                        'title' => $record->title,
                        'location' => $record->location,
                        'online_url' => $record->online_url,
                        'other_location' => $record->other_location,
                        'date' => $record->dt_start->format('d-m-Y'),
                        'start_time' => $record->dt_start->format('H:i'),
                        'end_time' => $record->dt_end->format('H:i'),
                        'description' => $record->description,
                    ]);
                }),
            DeleteAction::make()
        ];
    }

    public function eventDidMount(): string
    {
        return <<<'JS'
        function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
            el.setAttribute("x-tooltip", "tooltip");
            el.setAttribute("x-data", "{ tooltip: '"+event.extendedProps.appointmentTitle+"' }");
        }
    JS;
    }
}
