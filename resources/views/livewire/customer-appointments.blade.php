@use(Carbon\Carbon)

<div>
    @foreach($appointments as $appointment)
        <ul class="mb-4">
            <x-filament::card>
                @if($appointment->report)
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $appointment->report->title }}</h2>
                    <p class="text-xs text-gray-500 whitespace-pre-line dark:text-white">{{ $appointment->dt_start->format('d-m-Y') }}</p>
                    <p class="text-sm text-gray-700 dark:text-white whitespace-pre-line">{!! $appointment->report->description !!}</p>

                    <!-- Images Section -->
                    @if($appointment->report->hasImages())
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{__('portal.images')}}</h3>
                            <div class="flex flex-wrap dark:text-gray-400">
                                @foreach($appointment->report->getMedia('image-attachments') as $imageAttachment)
                                    <div class="w-1/3 p-1" x-data="{
                                        initLightbox(){
                                            const template = document.getElementById('report-image-{{$imageAttachment->id}}').innerHTML;
                                            const instance = window.basicLightbox.create(template);
                                            instance.show();
                                            window.currentBasicLightboxInstance = instance;
                                            }
                                        }"
                                         @click="initLightbox()">
                                        <img src="{{ $imageAttachment->getUrl() }}" alt="{{ $imageAttachment->name }}"
                                             class="w-full h-auto cursor-pointer rounded-md"
                                             data-template-id="report-image-{{$imageAttachment->id}}">
                                        <template id="report-image-{{$imageAttachment->id}}">
                                            <img src="{{ $imageAttachment->getUrl() }}"
                                                 alt="{{ $appointment->report->name }}"/>
                                            <div
                                                class="whitespace-pre-line text-gray-200 p-2 w-full text-center">{{ $appointment->report->name }}</div>
                                        </template>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Documents Section -->
                    @if($appointment->report->hasDocuments())
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{__('portal.documents')}}</h3>
                            <div class="flex flex-wrap dark:text-gray-400">
                                @foreach($appointment->report->getMedia('document-attachments') as $documentAttachment)
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

                    @if($appointment->report->sortedTasks->isNotEmpty())
                        <!-- Task Section -->
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{__('portal.tasks.tasks')}}</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-300 sm:pl-6">
                                            {{__('portal.tasks.title')}}
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-300">
                                            {{__('portal.complete_by')}}
                                        </th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">{{__('portal.status')}}</span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-800">
                                    @foreach($appointment->report->sortedTasks as $task)
                                        <tr class="bg-gray-100 dark:bg-gray-700">
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                                <a href="#" wire:click.prevent="toggleTaskCompletion({{ $task->id }})">
                                                    @if($task->dt_is_completed)
                                                        <x-heroicon-o-x-mark class="w-6 h-6 text-red-600 inline-block mr-2"/>
                                                    @else
                                                        <x-heroicon-o-check class="w-6 h-6 text-green-600 inline-block mr-2"/>
                                                    @endif
                                                </a>
                                                {{ $task->title }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 dark:text-gray-300">
                                                @if($task->dt_complete_by)
                                                    {{ Carbon::parse($task->dt_complete_by)->format('d-m-Y') }}
                                                @else

                                                @endif
                                            </td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @else
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $appointment->title }}</h2>
                    <p class="text-xs text-gray-500 whitespace-pre-line dark:text-white">{{ $appointment->dt_start->format('d-m-Y') }}</p>
                    <p class="text-sm text-gray-700 dark:text-white whitespace-pre-line">{!! $appointment->description !!}</p>
                @endif
            </x-filament::card>
        </ul>
    @endforeach
</div>
