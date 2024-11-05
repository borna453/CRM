<div class="max-w-sm rounded-xl p-2 overflow-hidden shadow-lg bg-white dark:bg-gray-800">
    <div class="px-4 py-2 flex justify-between items-center">
        <div class="font-bold text-xl text-gray-900 dark:text-gray-100">{{ $company->name }}</div>
        <x-filament::button
            href="{{ route('filament.admin.resources.companies.edit', $company->id) }}"
            tag="a"
            color="secondary"
            outlined="true"
            icon="heroicon-o-pencil">
        </x-filament::button>
    </div>

    <div class="px-4 py-3">
        <div class="mb-3">
            @if ($company->address)
                <p class="text-gray-700 dark:text-gray-300 text-sm">
                    {{ $company->address }}
                </p>
            @endif

            @if ($company->zip_code || $company->city)
                <p class="text-gray-700 dark:text-gray-300 text-sm">
                    {{ $company->zip_code }} {{ $company->city }}
                </p>
            @endif
        </div>

        @if ($company->email)
            <p class="text-gray-700 dark:text-gray-300 text-sm flex items-center mb-2">
                <x-heroicon-o-envelope class="w-5 h-5 mr-2"/>
                <a href="mailto:{{ $company->email }}" class="text-blue-500 dark:text-blue-400">{{ $company->email }}</a>
            </p>
        @endif

        @if ($company->phone_number)
            <p class="text-gray-700 dark:text-gray-300 text-sm flex items-center mb-2">
                <x-heroicon-o-phone class="w-5 h-5 mr-2"/>
                <a href="tel:{{ $company->phone_number }}" class="text-blue-500 dark:text-blue-400">{{ $company->phone_number }}</a>
            </p>
        @endif
    </div>
</div>
