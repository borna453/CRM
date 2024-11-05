<div class="w-[250px] bg-white border p-4 border-gray-200 rounded-lg shadow sm:p-6">
    <div class="flow-root">
        <ul role="list" class="divide-y-2 divide-gray-200">
            @forelse ($reports as $report)
                <li class="py-3 sm:py-4">
                    <a href="#report-{{$report->id}}"
                       class="block hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 transition duration-150 ease-in-out">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1 flex items-center justify-between">
                                <p class="w-3/4 text-sm font-medium text-gray-900 truncate">
                                    {{ $report->title }}
                                </p>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 text-right w-full">
                            {{$report->appointment->dt_start->format('d-m-Y')}}
                        </div>
                    </a>
                </li>
            @empty
                <li class="py-3 sm:py-4">
                    <p class="text-sm text-gray-900 dark:text-gray-100">
                        {{ __('portal.reports.no_reports') }}
                    </p>
                </li>
            @endforelse
        </ul>
    </div>
</div>
