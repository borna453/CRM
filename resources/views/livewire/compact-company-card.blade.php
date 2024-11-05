<div class="max-w-xs rounded-lg overflow-hidden shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
    <div class="flex justify-between items-center mb-3">
        <div class="font-bold text-base text-gray-900 dark:text-gray-100">{{ $company?->name }}</div>
        <button type="button" wire:click="hideCard" wire:click="$dispatch('showCompanySelectField')"
                class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div class="space-y-2">
        @if ($company?->address)
            <p class="text-gray-700 dark:text-gray-300 text-sm">
                {{ $company?->address }}
            </p>
        @endif

        @if ($company?->zip_code || $company?->city)
            <p class="text-gray-700 dark:text-gray-300 text-sm">
                {{ $company?->zip_code }} {{ $company?->city }}
            </p>
        @endif

        @if ($company?->email)
            <p class="text-gray-700 dark:text-gray-300 text-sm flex items-center">
                <x-heroicon-o-envelope class="w-4 h-4 mr-1"/>
                <a href="mailto:{{ $company?->email }}" class="text-blue-500 dark:text-blue-400">{{ $company?->email }}</a>
            </p>
        @endif

        @if ($company?->phone_number)
            <p class="text-gray-700 dark:text-gray-300 text-sm flex items-center">
                <x-heroicon-o-phone class="w-4 h-4 mr-1"/>
                <a href="tel:{{ $company?->phone_number }}" class="text-blue-500 dark:text-blue-400">{{ $company?->phone_number }}</a>
            </p>
        @endif
    </div>
</div>
