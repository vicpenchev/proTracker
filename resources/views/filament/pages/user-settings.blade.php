<x-filament::page>
    {{ $this->form }}

    <div class="flex justify-end mt-4">
        <x-filament::button wire:click="save" color="primary">
            Save Settings
        </x-filament::button>
    </div>
</x-filament::page>
