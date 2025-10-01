<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Add Activity Entry
        </x-slot>

        <div class="space-y-4">
            <p class="text-sm text-gray-600">
                Add entries to this project's MyHome activity stream.
            </p>
            
            {{ $this->addEntryAction }}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>