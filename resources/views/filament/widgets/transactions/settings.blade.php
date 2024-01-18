<x-filament-widgets::widget>
    <x-filament::section collapsible>
        <x-slot name="heading">
            {{ $this->heading ?? '' }}
        </x-slot>
        {{ $this->form }}
    </x-filament::section>
</x-filament-widgets::widget>
