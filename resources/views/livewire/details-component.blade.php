<div class="max-w-sm p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm sm:p-6">
    <div class="mb-4">
        <h6 class="text-lg font-semibold leading-tight text-gray-700 dark:text-white">{{ __('portal.appointments.title') }}</h6>
        <p class="text-md text-gray-700 dark:text-gray-400">{{ auth()->user()->isAdmin() ? $appointment->user->name : $appointment->title }}</p>
    </div>

    <hr class="border-gray-200 dark:border-gray-500 mb-2">

    <div class="mb-4">
        <h6 class="text-lg font-semibold leading-tight text-gray-700 dark:text-white">{{__('portal.appointments.description')}}</h6>
        <p class="text-md text-gray-700 dark:text-gray-400">{!! auth()->user()->isAdmin() ? $appointment->title : $appointment->description !!}</p>
    </div>

    <hr class="border-gray-200 dark:border-gray-500 mb-2">

    <div class="mb-4">
        <h6 class="text-lg font-semibold leading-tight text-gray-700 dark:text-white">{{ __('portal.date_time') }}</h6>
        <p class="text-md text-gray-700 dark:text-gray-400">
            {{ $appointment->dt_start->locale('nl')->isoFormat('D MMMM YYYY HH:mm') }} - {{ $appointment->dt_end->locale('nl')->isoFormat('HH:mm') }} uur
        </p>
    </div>
</div>
