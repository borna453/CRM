@include('livewire.base-appointments-calendar', [
    'componentId' => 'upcoming-appointments-calendar',
    'viewAllRoute' => route('filament.admin.resources.appointments.index', ['view' => 'timeGridWeek']),
    'appointmentRoute' => 'filament.admin.resources.appointments.view',
    'isAdminCalendar' => true
])
