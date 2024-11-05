@props([
    'activeLocale' => null,
    'activeManager',
    'content' => null,
    'managers',
    'ownerRecord',
    'pageClass',
])

<div class="fi-resource-relation-managers grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Customization: Changed flex to grid and added grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 -->
    @php
        $normalizeRelationManagerClass = function (string | Filament\Resources\RelationManagers\RelationManagerConfiguration $manager): string {
            return $manager instanceof \Filament\Resources\RelationManagers\RelationManagerConfiguration
                ? $manager->relationManager
                : $manager;
        };
    @endphp

    @foreach ($managers as $managerKey => $manager)
        <!-- Customization: Removed tabs and replaced with foreach loop -->
        @php
            $managerKey = (string) $managerKey;
            $isGroup = $manager instanceof \Filament\Resources\RelationManagers\RelationGroup;

            if ($isGroup) {
                $manager->ownerRecord($ownerRecord);
                $manager->pageClass($pageClass);
            } else {
                $manager = $normalizeRelationManagerClass($manager);
            }
        @endphp

        <div class="bg-white dark:bg-gray-800 shadow dark:shadow-lg rounded-lg p-4 dark:text-gray-100">
            <!-- Wrapped each manager in a styled div with padding and shadow for a card-like appearance -->
            <!-- Dark Mode Support: Added dark:bg-gray-800, dark:shadow-lg, and dark:text-gray-100 -->
            <div class="flex flex-col gap-y-4">
                @if ($isGroup)
                    @foreach ($manager->getManagers() as $groupedManagerKey => $groupedManager)
                        @php
                            $normalizedGroupedManagerClass = $normalizeRelationManagerClass($groupedManager);
                        @endphp

                        @livewire(
                            $normalizedGroupedManagerClass,
                            ['ownerRecord' => $ownerRecord, 'pageClass' => $pageClass],
                            key("{$normalizedGroupedManagerClass}-{$groupedManagerKey}"),
                        )
                    @endforeach
                @else
                    @livewire(
                        $manager,
                        ['ownerRecord' => $ownerRecord, 'pageClass' => $pageClass],
                        key($manager),
                    )
                @endif
            </div>
        </div>
    @endforeach

    @if ($content)
        <div class="bg-white dark:bg-gray-800 shadow dark:shadow-lg rounded-lg p-4 dark:text-gray-100">
            <!-- Added a styled div to wrap content in a card-like appearance -->
            <!-- Dark Mode Support: Added dark:bg-gray-800, dark:shadow-lg, and dark:text-gray-100 -->
            {{ $content }}
        </div>
    @endif
</div>
