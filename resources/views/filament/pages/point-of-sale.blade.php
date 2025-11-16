<x-filament-panels::page>
    <style>
        /* Override Filament page padding for full-width POS - scoped to this page only */
        .fi-page[data-page] {
            padding: 0 !important;
        }
        
        /* Ensure POS container doesn't affect Filament layout */
        .fi-page[data-page] .pos-container {
            isolation: isolate;
        }
    </style>
    @include('livewire.pos-component')
</x-filament-panels::page>

