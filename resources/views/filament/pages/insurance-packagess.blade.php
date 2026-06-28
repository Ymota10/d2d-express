<x-filament-panels::page>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

@foreach($this->packages as $package)

<div class="rounded-xl border bg-white shadow-sm overflow-hidden">

    <div class="p-6">

        <h2 class="text-xl font-bold">
            {{ $package->name }}
        </h2>

        <p class="text-gray-500 mt-1">
            {{ $package->name_ar }}
        </p>

        <div class="mt-6">

            <div class="text-3xl font-bold text-primary-600">
                {{ $package->percentage }}%
            </div>

            <div class="text-sm text-gray-500">
                Per Shipment
            </div>

        </div>

        <div class="mt-5 space-y-2 text-sm">

            <div>
                Minimum Fee:
                <strong>{{ number_format($package->minimum_fee,2) }} EGP</strong>
            </div>

            <div>
                Compensation:
                <strong>{{ number_format($package->max_compensation,0) }} EGP</strong>
            </div>

            <div>
                Covers Loss:
                {!! $package->covers_loss ? '✅' : '❌' !!}
            </div>

            <div>
                Covers Damage:
                {!! $package->covers_damage ? '✅' : '❌' !!}
            </div>

        </div>

    </div>

    <div class="border-t p-4">

    @if(!$package->percentage)

@if(auth()->user()->insurance_package_id == null)

    <x-filament::button
        color="gray"
        disabled
        class="w-full">

        Current Package

    </x-filament::button>

@else

    <x-filament::button
        color="gray"
        class="w-full"
        wire:click="subscribe({{ $package->id }})">

        Remove Insurance

    </x-filament::button>

@endif

@elseif(auth()->user()->insurance_package_id == $package->id)

<x-filament::button
    color="success"
    disabled
    class="w-full">

    Current Package

</x-filament::button>

@else

<x-filament::button
    color="primary"
    class="w-full"
    wire:click="subscribe({{ $package->id }})">

    Subscribe

</x-filament::button>

@endif

    </div>

</div>

@endforeach

</div>

</x-filament-panels::page>