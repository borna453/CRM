<div class="space-y-6 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md filament-card">
    @if($financialGoal)
        <!-- Top Bar with Input -->
        <div class="flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('portal.financial_report.progress') }}</h3>

            <div class="w-48">
                <div>
                    <label for="goal" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{__('portal.financial_report.goal')}}</label>
                    <div class="relative mt-2 rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm">€</span>
                        </div>
                        <input type="text" name="goal" id="goal"
                               class="block w-full rounded-md border-0 py-1.5 pl-8 pr-3 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 ring-1 ring-inset ring-gray-300 dark:ring-gray-600
                placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:focus:ring-indigo-400 sm:text-sm sm:leading-6"
                               placeholder="0.00"
                               wire:model.lazy="goal"
                               style="padding-left: 2rem;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="relative w-full bg-gray-200 dark:bg-gray-600 rounded-full h-6 overflow-hidden">
            <div
                class="h-full transition-all duration-300"
                style="width: {{ $progress }}%; background-color: {{ $progressColor }};">
            </div>
        </div>

        <!-- Status Text -->
        <div class="text-sm text-gray-500 dark:text-gray-400">
            <span class="font-medium text-gray-800 dark:text-gray-200">{{__('portal.financial_report.achieved')}}</span>
            €{{ number_format($achieved, 2) }} / €{{ number_format($goal, 2) }}
        </div>
    @else
        <x-filament::section collapsible>
            <x-slot name="heading">
                {{__('portal.financial_report.create_goal')}}
            </x-slot>

        <form wire:submit="create">
            {{ $this->form }}

            <x-filament::button class="mt-4" type="submit">
                {{__('portal.create')}}
            </x-filament::button>
        </form>
        </x-filament::section>
    @endif
</div>
