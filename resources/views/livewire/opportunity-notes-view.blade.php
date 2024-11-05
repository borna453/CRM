<div>
    @if($this->opportunity)
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('portal.notes.notes') }}</h3>
            <div>
                {{$this->createNoteAction}}
            </div>
        </div>
        <div class="p-4 mb-4 border rounded-lg shadow-sm bg-white dark:bg-gray-800 dark:border-gray-700 flex flex-col">
            @forelse($this->notes() as $note)
                <div class="pt-2 pb-2 {{ !$loop->last ? 'border-b border-gray-200 dark:border-gray-700' : '' }} break-all">
                    <p class="text-gray-800 dark:text-gray-200 mb-1">{!! $note->note !!}</p>
                    <div class="text-sm text-gray-500 dark:text-gray-400 self-end text-right mt-2 flex items-center justify-end space-x-2">
                        <p>{{ \Carbon\Carbon::parse($note->created_at)->format('d-m-Y H:i') }}, {{ $note->user->name }}</p>

                        @if($note->user->id === auth()->id())
                            <!-- Delete Button -->
                            <button wire:click.prevent="deleteNote({{ $note->id }})" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-500">
                                <x-heroicon-o-trash class="w-4 h-4"/>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400">{{__('portal.notes.empty_state')}}</p>
            @endforelse
        </div>
    @endif
    <x-filament-actions::modals :lazy="true"/>
</div>
