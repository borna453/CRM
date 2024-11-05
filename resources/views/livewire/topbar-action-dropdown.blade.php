<div>
    <div x-data="{ open: false }" class="relative inline-block text-left">
        <div>
            <button @click="open = !open" class="btn btn-primary flex items-center">
                <x-heroicon-s-bolt class="h-6 w-6 mt-1 text-yellow-300 hover:text-yellow-400" />
            </button>
        </div>

        <div x-show="open" @click.away="open = false" class="absolute right-0 z-10 mt-6 w-56 origin-top-right bg-white dark:bg-gray-800 shadow-lg rounded-lg">
            <div class="py-1 m-2 space-y-2">
                @if(auth()->user()->can('create', \App\Models\Appointment::class))
                    <button @click="$dispatch('open-modal', {id: 'create-appointment-modal'}); open = false;" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded flex flex-row-reverse justify-end">
                        <x-heroicon-o-calendar class="h-5 w-5 ml-2" />
                        <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                            {{ __('portal.appointments.create') }}
                        </span>
                    </button>
                @endif

                @if(((auth()->user()->isAdmin() || auth()->user()->isEmployee()) && auth()->user()->can('create', \App\Models\Task::class)) || (auth()->user()->isUser() && auth()->user()->can('create', \App\Models\Task::class)))
                    <button @click="$dispatch('open-modal', {id: 'create-task-modal'}); open = false;" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded flex flex-row-reverse justify-end">
                        <x-heroicon-o-list-bullet class="h-5 w-5 ml-2" />
                        <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                            {{ __('portal.tasks.create') }}
                        </span>
                    </button>
                @endif

                @if((auth()->user()->isAdmin() || auth()->user()->isEmployee()) && auth()->user()->can('create', \App\Models\Opportunity::class))
                    <button @click="$dispatch('open-modal', {id: 'create-opportunity-modal-dropdown'}); open = false;" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded flex flex-row-reverse justify-end">
                        <x-heroicon-o-map class="h-5 w-5 ml-2" />
                        <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                            {{ __('portal.opportunities.create') }}
                        </span>
                    </button>
                @endif

                @if((auth()->user()->isAdmin() || auth()->user()->isEmployee()) && auth()->user()->can('create', \App\Models\Company::class))
                    <a href="{{ route('filament.admin.resources.companies.create') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded flex flex-row-reverse justify-end">
                        <x-heroicon-o-briefcase class="h-5 w-5 ml-2" />
                        <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                            {{ __('portal.companies.create') }}
                        </span>
                    </a>
                @endif

                @if(auth()->user()->isUser() && auth()->user()->can('create', \App\Models\PinboardItem::class))
                    <button @click="$dispatch('open-modal', {id: 'create-pinboard-modal'}); open = false;" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded flex flex-row-reverse justify-end">
                        <x-heroicon-o-rectangle-stack class="h-5 w-5 ml-2" />
                        <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                            {{ __('portal.pinboard_items.create') }}
                        </span>
                    </button>
                @endif

                @if(auth()->user()->isOwner())
                    <a href="{{ \App\Filament\Owner\Resources\TenantResource::getUrl('create') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded flex flex-row-reverse justify-end">
                        <x-heroicon-o-user-group class="h-5 w-5 ml-2" />
                        <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                            {{ __('portal.tenant.create') }}
                        </span>
                    </a>
                @endif

                @if(auth()->user()->isAdmin() && auth()->user()->can('create', \App\Models\Message::class))
                    <button @click="$dispatch('open-modal', {id: 'message-modal'}); open = false;"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded flex flex-row-reverse justify-end">
                        <x-heroicon-o-chat-bubble-left-ellipsis class="h-5 w-5 ml-2" />
                        <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                            {{ __('portal.messages.create') }}
                        </span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Modals -->
    <x-filament::modal id="create-appointment-modal" width="4xl">
        @if(auth()->user()->can('create', \App\Models\Appointment::class))
            @livewire('appointment-modal-form')
        @endif
    </x-filament::modal>

    <x-filament::modal id="create-task-modal" width="4xl">
        @if(((auth()->user()->isAdmin() || auth()->user()->isEmployee()) && auth()->user()->can('create', \App\Models\Task::class)) || (auth()->user()->isUser() && auth()->user()->can('create', \App\Models\Task::class)))
            @livewire('task-modal-form')
        @endif
    </x-filament::modal>

    <x-filament::modal id="create-opportunity-modal-dropdown" width="4xl">
        @if((auth()->user()->isAdmin() || auth()->user()->isEmployee()) && auth()->user()->can('create', \App\Models\Opportunity::class))
            @livewire('opportunity-modal-form')
        @endif
    </x-filament::modal>

    <x-filament::modal id="create-pinboard-modal" width="4xl">
        @if(auth()->user()->isUser() && auth()->user()->can('create', \App\Models\PinboardItem::class))
        @livewire('pinboard-item-modal-form')
        @endif
    </x-filament::modal>

    <x-filament::modal id="message-modal" width="4xl">
        @if(auth()->user()->isAdmin() && auth()->user()->can('create', \App\Models\Message::class))
            @livewire('message-modal-form')
        @endif
    </x-filament::modal>
</div>
