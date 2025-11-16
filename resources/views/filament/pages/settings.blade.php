<x-filament-panels::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}

        <div class="mt-6 flex gap-3 items-center">
            <x-filament::button
                type="submit"
                color="success"
                size="lg"
                icon="heroicon-o-check"
            >
                Save Settings
            </x-filament::button>
            
            <span 
                wire:loading 
                wire:target="save"
                class="text-sm text-gray-500"
            >
                Saving...
            </span>
        </div>
    </form>
</x-filament-panels::page>

