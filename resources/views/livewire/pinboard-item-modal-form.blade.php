<div x-data x-init="
        $wire.on('closeModal', () => {
            $dispatch('close-modal', { id: 'create-pinboard-modal' });
        });
    ">
    <x-slot name="heading">
        {{ __('portal.pinboard_items.create') }}
    </x-slot>
    <form wire:submit.prevent="create">
        {{ $this->form }}
        <x-filament::button class="mt-4" type="submit">
            {{__('portal.create')}}
        </x-filament::button>
    </form>

    <x-filament-actions::modals />
</div>
