<div class="relative p-6 bg-white dark:bg-gray-800 rounded-lg">
    <!-- Close button in the top right corner -->
    <button class="absolute top-1 right-1 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none" wire:click="$dispatch('close-modal', { id: 'suggest-tasks-modal' } )">
        <x-heroicon-o-x-mark class="w-5 h-5" />
    </button>


    @if(!empty($tasks))
        <div class="max-h-96 overflow-y-auto">
            <table class="w-full bg-white dark:bg-gray-800 shadow-md rounded-lg mt-4">
                <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-900 dark:text-gray-100">{{ __('portal.tasks.tasks') }}</th>
                    <th class="px-4 py-2 text-center text-gray-900 dark:text-gray-100">{{ __('portal.tasks.actions') }}</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800">
                @foreach ($tasks as $index => $task)
                    <tr class="border-t border-gray-200 dark:border-gray-700">
                        <!-- Task Details Column -->
                        <td class="px-4 py-4">
                            <input
                                type="text"
                                wire:model="tasks.{{ $index }}.title"
                                class="font-semibold text-gray-900 dark:text-gray-100 bg-transparent border-none focus:border-none focus:outline-none focus:ring-0 p-0 max-w-full break-words truncate"
                                style="width: 100%; max-width: 100%; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word;"

                            />
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <input
                                    type="text"
                                    wire:model.live="tasks.{{ $index }}.responsible"
                                    class="bg-transparent text-gray-500 dark:text-gray-400 border-none focus:border-none focus:outline-none focus:ring-0 p-0"
                                />
                                <input
                                    type="date"
                                    wire:model.live="tasks.{{ $index }}.completed_by"
                                    class="bg-transparent text-gray-500 dark:text-gray-400 border-none focus:border-none focus:outline-none focus:ring-0 p-0"
                                />
                            </div>
                        </td>

                        <!-- Actions Column -->
                        <td class="px-4 py-4 text-center">
                            <div class="flex justify-center space-x-4">
                                <!-- Checkmark Icon Button -->
                                <button
                                    wire:click="createTask({{ $index }})"
                                    class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-600 focus:outline-none"
                                    title="{{ __('portal.tasks.create') }}"
                                >
                                    <x-heroicon-o-plus class="w-5 h-5 inline" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-center text-gray-500 dark:text-gray-400">{{ __('portal.tasks.empty_state') }}</p>
    @endif
    <div class="flex justify-center items-center mt-4">
        <div class="cursor-pointer bg-gray-400 hover:bg-gray-600 text-white font-bold w-fit p-2 rounded-lg" wire:click="$dispatch('close-modal', { id: 'suggest-tasks-modal' } )">
            {{__('portal.close')}}
        </div>
        <div wire:click="createAllTasks" class="ml-12 cursor-pointer bg-primary-500 hover:bg-primary-600 font-bold text-white w-fit p-2 rounded-lg">
            {{{__('portal.create_all_tasks')}}}
        </div>
    </div>
</div>
