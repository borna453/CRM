<div class="p-4 space-y-6">
    <!-- Collapsible Original Message at the Top -->
    <div x-data="{ isOpen: true }">
        <div class="flex justify-center">
            <div class="w-full md:w-2/3 lg:w-1/2 p-4 bg-gray-200 dark:bg-gray-700 rounded-lg">
                <!-- Title and Toggle Button -->
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-200 text-center w-full">
                        {{ $originalMessage['title'] }}
                    </h2>
                    <button @click.prevent="isOpen = !isOpen" class="text-gray-500 dark:text-gray-300">
                        <x-heroicon-s-chevron-down x-show="!isOpen" class="h-6 w-6 transition-transform duration-300 ease-in-out" />
                        <x-heroicon-s-chevron-up x-show="isOpen" class="h-6 w-6 transition-transform duration-300 ease-in-out" />
                    </button>
                </div>
                <!-- Collapsible Content -->
                <div x-show="isOpen" class="prose max-w-full text-gray-700 dark:text-gray-300 mt-2 text-left">
                    {!! $originalMessage['content'] !!}
                </div>
                <p class="text-sm text-center text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('portal.messages.sent_by') }}: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $originalMessage->creator->name }}</span>, {{ $originalMessage['formatted_created_at'] }}
                </p>
            </div>
        </div>
    </div>

    <!-- Conversation Replies -->
    <div
        x-data="{
            scroll: () => { $el.scrollTo(0, $el.scrollHeight); },
            scrollToBottom() {
                this.$nextTick(() => {
                    this.$refs.replyList.scrollTo(0, this.$refs.replyList.scrollHeight);
                });
            },
            init() {
                this.scrollToBottom();

                Livewire.on('replyAdded', () => {
                    this.scrollToBottom();
                });
            }
        }"
        x-intersect="scroll()"
        class="space-y-4 max-h-[50vh] overflow-y-auto px-4"
        x-ref="replyList">

        @foreach($replies as $reply)
            <div class="flex {{ $this->getReplyAlignmentClass($reply) }}">
                <div class="p-3 border rounded-lg {{ $this->getReplyBackgroundClass($reply) }} max-w-xs relative">
                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-300 mb-1">
                        {{ $reply->creator->name }}
                    </p>
                    <div class="prose max-w-full text-gray-700 dark:text-gray-200">
                        {!! $reply['content'] !!}
                    </div>
                    <div class="flex items-center {{ $this->getReplyJustificationClass($reply) }} space-x-2 mt-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $reply['formatted_created_at'] }}
                        </p>

                        @if($loop->last)
                            @if(auth()->user()->isUser() && !$reply->creator->isAdmin())
                                <!-- Show icons for the last message when it's not sent by an admin -->
                                @if($reply->seen_at)
                                    <x-heroicon-o-eye class="w-4 h-4 text-blue-500" />
                                @else
                                    <x-heroicon-o-check class="w-4 h-4 text-gray-500" />
                                @endif
                            @elseif(auth()->user()->isAdmin() && $reply->creator->isAdmin())
                                <!-- Show icons for admins when the last message is from another admin -->
                                @if($reply->seen_at)
                                    <x-heroicon-o-eye class="w-4 h-4 text-blue-500" />
                                @else
                                    <x-heroicon-o-check class="w-4 h-4 text-gray-500" />
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex justify-center mt-6">
        <div class="w-full">
            {{ $this->form }}
            <div class="flex justify-end">
                <x-filament::button wire:click.prevent="submitReply" class="mt-4 btn btn-primary">
                    {{ __('portal.messages.reply') }}
                </x-filament::button>
            </div>
        </div>
    </div>
</div>
