@include('livewire.base-appointments-calendar', [
    'componentId' => 'user-upcoming-appointments-calendar',
    'viewAllRoute' => route('filament.user.resources.appointments.index'),
    'appointmentRoute' => 'filament.user.resources.appointments.view',
    'isAdminCalendar' => false
])
