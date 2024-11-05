<div>
        @foreach($pinboardItems as $pinboardItem)
            <ul class="mb-4 mt-4">
                <x-filament::card>
                    <li class="text-md font-bold text-gray-900 dark:text-gray-200">{{ $pinboardItem->title }}</li>
                    <li class="text-sm text-gray-700 dark:text-white whitespace-pre-line">{!! $pinboardItem->description !!}</li>
                </x-filament::card>
            </ul>
        @endforeach
</div>
