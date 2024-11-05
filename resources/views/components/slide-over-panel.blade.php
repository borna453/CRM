<div class="fixed inset-0 overflow-hidden " x-show="{{ $openVariable }}" @keydown.window.escape="{{ $openVariable }} = false" style="z-index: 40;">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute inset-0 bg-black bg-opacity-25 transition-opacity" @click="{{ $openVariable }} = false" x-show="{{ $openVariable }}" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
        <div class="fixed inset-y-0 right-0 pl-10 {{ $widthClass }} flex ">
            <div class="w-screen {{ $widthClass }} transform transition ease-in-out duration-300" x-show="{{ $openVariable }}" x-transition:enter="ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                <div class="h-full flex flex-col bg-white shadow-xl overflow-y-scroll dark:bg-gray-900">
                    <div class="px-4 py-6 sm:px-6">
                        <div class="flex justify-between items-center mb-4" >
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ $title }}</h2>
                            <x-filament::button @click="open = false" class="text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </x-filament::button>
                        </div>
                    </div>
                    {{ $content }}
                </div>
            </div>
        </div>
    </div>
</div>
