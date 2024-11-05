<div>
    @if($this->opportunity)
        <!-- Task Section -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{__('portal.tasks.tasks')}}</h3>
            <div>
                {{  $this->createTaskAction }}
            </div>
        </div>
        @if($this->tasks()->isNotEmpty())
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
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
                        <th scope="col"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-300">
                            {{__('portal.users.user')}}
                        </th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                            <span class="sr-only">{{__('portal.status')}}</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600 bg-white dark:bg-gray-800">
                    @foreach($this->tasks() as $task)
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                <a href="#" wire:click.prevent="toggleTaskCompletion({{ $task->id }})">
                                    @if($task->dt_is_completed)
                                        <x-heroicon-o-x-mark class="w-6 h-6 text-red-600 dark:text-red-500 inline-block mr-2"/>
                                    @else
                                        <x-heroicon-o-check class="w-6 h-6 text-green-600 dark:text-green-500 inline-block mr-2"/>
                                    @endif
                                </a>
                                {{ $task->title }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 dark:text-gray-300">
                                @if($task->dt_complete_by)
                                    {{ \Carbon\Carbon::parse($task->dt_complete_by)->format('d-m-Y') }}
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $task->user->name }}
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 p-4 mb-4 border rounded-lg dark:text-gray-400 dark:border-gray-700">{{__('portal.tasks.empty_state')}}</p>
        @endif
    @endif
    <x-filament-actions::modals/>
</div>
