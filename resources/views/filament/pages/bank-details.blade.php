<x-filament-panels::page>

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
        <x-filament::button
    type="submit"
    form="form"
    wire:loading.attr="disabled"
    wire:target="save"
>
    <span wire:loading.remove wire:target="save">
        Save Bank Details
    </span>

    <span wire:loading wire:target="save">
        Saving...
    </span>
</x-filament::button>
        </div>
    </form>

</x-filament-panels::page>