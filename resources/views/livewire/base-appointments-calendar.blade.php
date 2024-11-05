@php
    /** @var \Illuminate\Support\Collection<string, \Illuminate\Database\Eloquent\Collection<int, \App\Models\Appointment>> $upcomingAppointments */
    /** @var \App\Models\Appointment $appointment */
@endphp

<x-filament::widget id="{{ $componentId }}">
    <div class="p-4 mb-4 h-full bg-white rounded-lg shadow sm:p-6 dark:bg-gray-800">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold leading-none text-blue-1000 dark:text-white">{{ __('portal.appointments.appointments') }}</h3>
            <x-filament::button href="{{ $viewAllRoute }}" tag="a" outlined color="secondary">
                {{ __('portal.view_all') }}
            </x-filament::button>
        </div>
        <hr class="my-4 border">
        <div class="flow-root">
            <div class="flex gap-8 flex-col sm:flex-row" x-data="{}">
                <div
                    class="flex-shrink-0 flex justify-center align-middle"
                    id="leadCal"
                    x-ref="leadCal"
                    inline-datepicker
                    data-date="{{ now()->format('d-m-Y') }}"
                    datepicker-format="d-m-Y"
                    datepicker-language="{{ app()->getLocale() }}"></div>

                <div class="flex-grow -ml-2">
                    @foreach($this->upcomingAppointments->slice(0,3) as $groupLabel => $appointments)
                        <div class="pt-3 pb-1 text-neutral-500 first:pt-0">{{ $groupLabel }}</div>
                        @foreach($appointments as $appointment)
                            <a href="{{ route($appointmentRoute, ['record' => $appointment->id]) }}" class="p-4 mb-1 border border-primary-600 text-primary-600 rounded-md flex justify-between shadow hover:bg-primary-50">
                                <div>
                                    <div class="font-semibold">
                                        {{ $isAdminCalendar ? $appointment->user->name : $appointment->title }}
                                    </div>
                                </div>
                                <div>{{ $appointment->dt_start->format('H:i')}}</div>
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="allUpcomingAppointments">@json($allUpcomingAppointmentDates)</script>

    @script
    <script>
        window.setAppointmentsOnCalendar = function (timestamps) {
            document.querySelectorAll('.datepicker-cell').forEach((element) => {
                const datetime = new Date(Number(element.dataset.date));
                let hasAppointment = false;

                for (const appointment of timestamps) {
                    if (datetime.getFullYear() === appointment.getFullYear() &&
                        datetime.getMonth() === appointment.getMonth() &&
                        datetime.getDate() === appointment.getDate()
                    ) {
                        const marker = document.createElement('span');
                        marker.classList.add('datepicker-event-marker');
                        element.appendChild(marker);
                        hasAppointment = true;
                        element.classList.add('datepicker-cell-clickable');

                        break;
                    }
                }

                if (hasAppointment) {
                    element.addEventListener('click', (e) => {
                        e.preventDefault();

                        const utcDate = new Date(Date.UTC(datetime.getFullYear(), datetime.getMonth(), datetime.getDate()));

                        const date = utcDate.toISOString().split('T')[0];

                        window.open(`{{ $viewAllRoute }}?date=` + date, '_blank');
                    });
                }
            });
        }

        let timeout = null;
        const callback = () => {
            window.initDatePicker(document.getElementById('leadCal'));
            const allUpcomingAppointments = JSON.parse(document.getElementById('allUpcomingAppointments').innerText);
            window.setAppointmentsOnCalendar(allUpcomingAppointments.map((timestamp) => new Date(timestamp)));
        };

        Livewire.hook('morph.updated', ({ component }) => {
            if (component.el.id === '{{ $componentId }}') {
                clearTimeout(timeout);
                timeout = setTimeout(callback, 100);
            }
        });
    </script>
    @endscript
</x-filament::widget>
