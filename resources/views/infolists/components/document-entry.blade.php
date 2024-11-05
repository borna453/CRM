<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $state = $getState();

        if ($state instanceof \Illuminate\Support\Collection) {
            $state = $state->all();
        }

        $state = \Illuminate\Support\Arr::wrap($state);

        $limit = $getLimit();
        $limitedState = array_slice($state, 0, $limit);

        $stateCount = count($state);
        $limitedStateCount = count($limitedState);

        $defaultDocumentUrl = $getDefaultDocumentUrl();

        if ((! $limitedStateCount) && filled($defaultDocumentUrl)) {
            $limitedState = [null];
            $limitedStateCount = 1;
        }

        $hasLimitedRemainingText = $hasLimitedRemainingText() && ($limitedStateCount < $stateCount);
        $isLimitedRemainingTextSeparate = $isLimitedRemainingTextSeparate();

        $limitedRemainingTextSizeClasses = match ($getLimitedRemainingTextSize()) {
            'xs' => 'text-xs',
            'sm', null => 'text-sm',
            'base', 'md' => 'text-base',
            'lg' => 'text-lg',
            default => $size,
        };

        $documentNames = $entry->getDocumentNames();
    @endphp

    <div
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class([
                    'fi-in-document flex flex-col gap-y-2',
                ])
        }}
    >
        @if ($limitedStateCount)
            <div class="flex flex-wrap gap-4">
                @foreach ($limitedState as $index => $stateItem)
                    @php
                        $documentName = $documentNames[$index] ?? 'Document ' . ($index + 1);
                    @endphp
                    <a
                        href="{{ filled($stateItem) ? $getDocumentUrl($stateItem) : $defaultDocumentUrl }}"
                        target="_blank"
                        class="block border border-gray-300 bg-gray-100 dark:bg-gray-800 rounded-md p-2 text-medium hover:bg-gray-200 dark:hover:bg-gray-700"
                    >
                        {{ $documentName }}
                    </a>
                @endforeach

                @if ($hasLimitedRemainingText && (! $isLimitedRemainingTextSeparate))
                    <div
                        @class([
                            'font-medium text-gray-500 dark:text-gray-400',
                            $limitedRemainingTextSizeClasses,
                        ])
                    >
                        +{{ $stateCount - $limitedStateCount }}
                    </div>
                @endif
            </div>

            @if ($hasLimitedRemainingText && $isLimitedRemainingTextSeparate)
                <div
                    @class([
                        'font-medium text-gray-500 dark:text-gray-400',
                        $limitedRemainingTextSizeClasses,
                    ])
                >
                    +{{ $stateCount - $limitedStateCount }}
                </div>
            @endif
        @elseif (($placeholder = $getPlaceholder()) !== null)
            <x-filament-infolists::entries.placeholder>
                {{ $placeholder }}
            </x-filament-infolists::entries.placeholder>
        @endif
    </div>
</x-dynamic-component>
