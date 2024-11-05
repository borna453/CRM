@use(Carbon\Carbon)

<x-filament-panels::page>
    <div>
        @foreach($reports as $report)
            <ul class="mb-4">
                <x-filament::card>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $report->title }}</h2>
                    <p class="text-xs text-gray-500 whitespace-pre-line dark:text-white">{{ $report->appointment->dt_start->format('d-m-Y') }}</p>
                    <p class="text-sm text-gray-700 dark:text-white whitespace-pre-line">{!! $report->description !!}</p>

                    <!-- Images Section -->
                    @if($report->hasImages())
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{__('portal.images')}}</h3>
                            <div class="flex flex-wrap dark:text-gray-400">
                                @foreach($report->getMedia('image-attachments') as $imageAttachment)
                                    <div class="w-1/3 p-1">
                                        <img src="{{ $imageAttachment->getUrl() }}" alt="{{ $imageAttachment->name }}"
                                             class="w-full h-auto cursor-pointer rounded-md"
                                             @click="window.basiclightbox.create(document.getElementById('report-image-{{$imageAttachment->id}}')).show()">
                                        <template id="report-image-{{$imageAttachment->id}}">
                                            <img src="{{ $imageAttachment->getUrl() }}" alt="{{ $report->name }}"/>
                                            <div class="whitespace-pre-line text-gray-200 p-2 w-full text-center">{{ $report->name }}</div>
                                        </template>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Documents Section -->
                    @if($report->hasDocuments())
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{__('portal.documents')}}</h3>
                            <div class="flex flex-wrap dark:text-gray-400">
                                @foreach($report->getMedia('document-attachments') as $documentAttachment)
                                    <div class="w-full sm:w-1/2 lg:w-1/3 p-1">
                                        <a href="{{ $documentAttachment->getUrl() }}"
                                           class="block p-4 bg-gray-100 border border-gray-200 rounded hover:bg-gray-200 dark:text-white dark:bg-gray-900"
                                           download="{{ $documentAttachment->name }}">
                                            Download: {{ $documentAttachment->name }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($report->sortedTasks->isNotEmpty())
                        <!-- Task Section -->
                        <div class="mt-4 w-full">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{__('portal.tasks.tasks')}}</h3>
                            <div class="overflow-x-auto w-full">
                                <table class="min-w-full divide-y divide-gray-300 w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-700 w-full">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-300 sm:pl-6">
                                            {{__('portal.tasks.title')}}
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-300">
                                            {{__('portal.complete_by')}}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-800 w-full">
                                    @foreach($report->sortedTasks as $task)
                                        <tr class="bg-gray-100 dark:bg-gray-700 w-full">
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                                @if($task->dt_is_completed)
                                                    <x-heroicon-o-check class="w-6 h-6 text-green-600 inline-block mr-2" />
                                                @else
                                                    <x-heroicon-o-x-mark class="w-6 h-6 text-red-600 inline-block mr-2" />
                                                @endif
                                                {{ $task->title }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 dark:text-gray-300">
                                                @if($task->dt_complete_by)
                                                    {{ Carbon::parse($task->dt_complete_by)->format('d-m-Y') }}
                                                @else

                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </x-filament::card>
            </ul>
        @endforeach
    </div>
</x-filament-panels::page>
