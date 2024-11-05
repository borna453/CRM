<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($groups as $groupName => $permissions)
        <livewire:permission-card
            :title="$groupName"
            :permissions="$permissions"
            :roleName="$roleName"
            :tenantId="$tenantId"
            wire:key="{{ $roleName . '-' . $tenantId . '-' . $groupName }}"
        />
    @endforeach
</div>
