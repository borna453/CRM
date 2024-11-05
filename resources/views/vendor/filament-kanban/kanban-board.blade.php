<x-filament-panels::page>
    <div class="flex justify-center">
        <div x-data wire:ignore.self class="md:flex overflow-x-auto overflow-y-hidden gap-4 pb-4">
            @foreach($statuses as $status)
                @include(static::$statusView)
            @endforeach

            <div wire:ignore>
                @include(static::$scriptsView)
            </div>
        </div>

        <x-filament::modal id="choice-opportunity">
            <div class="flex items-center">
                <div class="flex-grow text-center font-bold text-lg">
                    {{__('portal.opportunities.choose_option')}}
                </div>
                <div wire:click="closeModal" class="ml-auto cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>

                </div>
            </div>
            <div class="text-gray-500">
                {{__('portal.opportunities.choose_option_description')}}
            </div>
            <div class="flex justify-center">
                <x-filament::button wire:click="archiveOpportunity({{ $selectedRecordId }})"
                                    class="w-full rounded-lg py-2 outline outline-2 outline-offset-2 outline-slate-500">
                    <div class="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                        </svg>
                        <div class="ml-2 mt-0.5">
                            {{__('portal.archive')}}
                        </div>
                    </div>
                </x-filament::button>
            </div>
            @if(\App\Models\Opportunity::where('id', $selectedRecordId)->first()?->company_id)
                <div class="flex justify-center">
                    <x-filament::button wire:click="winOpportunity"
                                        class="w-full rounded-lg py-2 outline outline-2 outline-offset-2 outline-slate-500">
                        <div class="flex">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0"/>
                            </svg>
                            <div class="ml-2 mt-0.5">
                                {{__('portal.opportunities.won')}}
                            </div>
                        </div>
                    </x-filament::button>
                </div>
            @endif
        </x-filament::modal>
        @unless($disableEditModal)
            <x-filament-kanban::edit-record-modal/>
        @endunless
    </div>
    <x-filament::modal id="create-opportunity-modal-kanban" width="4xl">
        <livewire:opportunity-modal-form :lazy="true"/>
    </x-filament::modal>
</x-filament-panels::page>
