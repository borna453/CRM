<div class="filament-card mt-4">
    <div class="mt-4 overflow-x-auto rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600 rounded-lg overflow-hidden">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col"
                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-300 sm:pl-6">
                    {{ __('portal.name') }}
                </th>
                <th scope="col"
                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-300">
                    {{ __('portal.messages.seen_at') }}
                </th>
                <th scope="col"
                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-300"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-600 bg-white dark:bg-gray-800">
            @foreach($recipients as $recipient)
                <tr class="bg-gray-100 dark:bg-gray-700">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                        {{ $recipient->user->name }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 dark:text-gray-300">
                        @if($recipient->seen_at)
                            {{ Carbon\Carbon::parse($recipient->seen_at)->diffForHumans() }}
                        @else
                            {{ __('portal.messages.not_seen') }}
                        @endif
                    </td>
                    @if($recipient->user_replies_count > 0)
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 dark:text-gray-300">
                            <button
                                wire:click.prevent="openConversationModal('{{ $recipient->user_id }}')"
                                class="px-3 py-2 bg-blue-500 text-white text-sm rounded-lg">
                                {{ __('portal.messages.view_conversation') }}
                            </button>
                        </td>
                    @else
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 dark:text-gray-300"> </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
        @if ($recipients->hasPages())
            <div class="mt-4 p-4 bg-white dark:bg-gray-800">
                {{ $recipients->links() }}
            </div>
        @endif
    </div>
    <x-filament::modal
        width="5xl"
        id="conversation-modal"
        wire:close-by-clicking-away="unsetUserId"
        wire:keydown.escape="unsetUserId"
    >
        @if($userId)
            <livewire:conversation-modal-view :message-id="$messageId" :user-id="$userId"/>
        @endif
        <x-slot name="footerActions">
            <x-filament::button wire:click="$dispatch('close-modal', { id: 'conversation-modal' })" color="secondary">
                {{ __('portal.close') }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
