<div class="p-4 bg-white dark:bg-gray-800 shadow rounded-md">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $title }}</h3>
        <span
            wire:click="toggleAll"
            class="cursor-pointer font-semibold text-sm text-custom-600 dark:text-custom-400 hover:underline focus:underline"
            style="--c-400:var(--primary-400); --c-600:var(--primary-600);"
        >
            {{ $allSelected ? __('portal.permissions.deselect_all') : __('portal.permissions.select_all') }}
        </span>
    </div>

    <div class="space-y-2">
        @foreach($permissions as $permission)
            <div class="flex items-center">
                <input
                    type="checkbox"
                    @if(in_array($permission->value, $checkedPermissions)) checked @endif
                    wire:click="togglePermission('{{ $permission->value }}')"
                    id="{{ $permission->value }}"
                    class="form-checkbox h-5 w-5 text-primary-600 dark:text-primary-400 border-gray-300 dark:border-gray-600 rounded"
                />
                <label for="{{ $permission->value }}" class="ml-3 text-sm font-medium text-gray-600 dark:text-gray-300">
                    {{ $this->formatPermissionLabel($permission->value) }}
                </label>
            </div>
        @endforeach
    </div>
</div>
