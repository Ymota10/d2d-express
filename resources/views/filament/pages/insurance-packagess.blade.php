<x-filament-panels::page>

    {{-- Page Header --}}
    <div class="mb-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-100 text-primary-600">
                        <healthicons-o-truck-driver class="h-5 w-5" />
                    </div>

                    <div class="flex items-center gap-2 mb-2">
    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-100 text-primary-600">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            class="h-5 w-5"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6l7-3z"
            />
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 12l2 2 4-4"
            />
        </svg>
    </div>

    <span class="text-sm font-semibold uppercase tracking-wider text-primary-600">
        Shipment Protection
    </span>
</div>
                </div>

                <h1 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Insurance Packages
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500 dark:text-gray-400">
                    Protect your shipments with flexible insurance coverage designed
                    for your business.
                </p>
            </div>

        </div>
    </div>


    {{-- Packages --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">

        @foreach($this->packages as $package)

            @php
                $isCurrent = auth()->user()->insurance_package_id == $package->id;
                $isFree = !$package->percentage;

                // Highlight the first paid package as recommended.
                $isRecommended = !$isFree && $loop->iteration === 2;
            @endphp

            <div
                class="group relative flex flex-col overflow-hidden rounded-2xl border
                {{ $isCurrent
                    ? 'border-primary-500 ring-2 ring-primary-500/20'
                    : 'border-gray-200 dark:border-gray-700'
                }}
                bg-white shadow-sm transition-all duration-300
                hover:-translate-y-1 hover:shadow-xl
                dark:bg-gray-900"
            >

                {{-- Recommended Badge --}}
                @if($isRecommended && !$isCurrent)
                    <div class="absolute right-4 top-4 z-10">
                        <span class="inline-flex items-center gap-1.5 rounded-full
                            bg-primary-600 px-3 py-1 text-xs font-semibold text-white shadow-sm">

                            <x-heroicon-s-star class="h-3.5 w-3.5" />

                            Recommended
                        </span>
                    </div>
                @endif


                {{-- Current Package Badge --}}
                @if($isCurrent)
                    <div class="absolute right-4 top-4 z-10">
                        <span class="inline-flex items-center gap-1.5 rounded-full
                            bg-success-100 px-3 py-1 text-xs font-semibold text-success-700
                            dark:bg-success-500/10 dark:text-success-400">

                            <x-heroicon-s-check-circle class="h-3.5 w-3.5" />

                            Current Plan
                        </span>
                    </div>
                @endif


                {{-- Card Header --}}
                <div class="relative overflow-hidden p-6">

                    {{-- Decorative background --}}
                    <div class="pointer-events-none absolute -right-12 -top-12
                        h-32 w-32 rounded-full bg-primary-50
                        dark:bg-primary-500/5">
                    </div>

                    <div class="relative">

                        {{-- Package Icon --}}
                        <div class="mb-5 flex h-12 w-12 items-center justify-center
                            rounded-2xl bg-primary-50 text-primary-600
                            transition-transform duration-300
                            group-hover:scale-110
                            dark:bg-primary-500/10 dark:text-primary-400">

                            @if($isFree)

                                <healthicons-o-truck-driver class="h-6 w-6" />

                            @else

                                <healthicons-o-truck-driver class="h-6 w-6" />

                            @endif

                        </div>


                        {{-- Package Name --}}
                        <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                            {{ $package->name }}
                        </h2>

                        @if($package->name_ar)
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $package->name_ar }}
                            </p>
                        @endif


                        {{-- Percentage --}}
                        <div class="mt-7 flex items-end gap-2">

                            <span class="text-4xl font-black tracking-tight text-primary-600 dark:text-primary-400">
                                {{ $package->percentage ?? 0 }}%
                            </span>

                            <span class="mb-1.5 text-sm text-gray-500 dark:text-gray-400">
                                per shipment
                            </span>

                        </div>

                    </div>
                </div>


                {{-- Divider --}}
                <div class="mx-6 border-t border-gray-100 dark:border-gray-800"></div>


                {{-- Features --}}
                <div class="flex-1 p-6">

                    <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Package Benefits
                    </p>

                    <div class="space-y-4">

                        {{-- Minimum Fee --}}
                        <div class="flex items-center justify-between gap-4">

                            <div class="flex items-center gap-3">

                                <div class="flex h-9 w-9 items-center justify-center rounded-xl
                                    bg-gray-50 text-gray-500
                                    dark:bg-gray-800 dark:text-gray-400">

                                    <x-heroicon-o-banknotes class="h-5 w-5" />

                                </div>

                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                    Minimum Fee
                                </span>

                            </div>

                            <span class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ number_format($package->minimum_fee, 2) }}
                                <span class="text-xs font-normal text-gray-400">EGP</span>
                            </span>

                        </div>


                        {{-- Compensation --}}
                        <div class="flex items-center justify-between gap-4">

                            <div class="flex items-center gap-3">

                                <div class="flex h-9 w-9 items-center justify-center rounded-xl
                                    bg-gray-50 text-gray-500
                                    dark:bg-gray-800 dark:text-gray-400">

                                    <x-heroicon-o-currency-dollar class="h-5 w-5" />

                                </div>

                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                    Max Compensation
                                </span>

                            </div>

                            <span class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ number_format($package->max_compensation, 0) }}
                                <span class="text-xs font-normal text-gray-400">EGP</span>
                            </span>

                        </div>


                        {{-- Covers Loss --}}
                        <div class="flex items-center justify-between">

                            <div class="flex items-center gap-3">

                                <div class="flex h-9 w-9 items-center justify-center rounded-xl
                                    bg-gray-50 text-gray-500
                                    dark:bg-gray-800 dark:text-gray-400">

                                    <x-heroicon-o-archive-box class="h-5 w-5" />

                                </div>

                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                    Loss Protection
                                </span>

                            </div>

                            @if($package->covers_loss)

                                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-success-600">
                                    <x-heroicon-s-check-circle class="h-5 w-5" />
                                    Covered
                                </span>

                            @else

                                <span class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-400">
                                    <x-heroicon-s-x-circle class="h-5 w-5" />
                                    Not Covered
                                </span>

                            @endif

                        </div>


                        {{-- Covers Damage --}}
                        <div class="flex items-center justify-between">

                            <div class="flex items-center gap-3">

                                <div class="flex h-9 w-9 items-center justify-center rounded-xl
                                    bg-gray-50 text-gray-500
                                    dark:bg-gray-800 dark:text-gray-400">

                                    <x-heroicon-o-wrench-screwdriver class="h-5 w-5" />

                                </div>

                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                    Damage Protection
                                </span>

                            </div>

                            @if($package->covers_damage)

                                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-success-600">
                                    <x-heroicon-s-check-circle class="h-5 w-5" />
                                    Covered
                                </span>

                            @else

                                <span class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-400">
                                    <x-heroicon-s-x-circle class="h-5 w-5" />
                                    Not Covered
                                </span>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- CTA --}}
                <div class="border-t border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-800/40">

                    @if($isFree)

                        @if(auth()->user()->insurance_package_id == null)

                            <x-filament::button
                                color="gray"
                                disabled
                                class="w-full">

                                <x-heroicon-s-check-circle class="mr-1.5 h-4 w-4" />

                                Current Package

                            </x-filament::button>

                        @else

                            <x-filament::button
                                color="gray"
                                class="w-full"
                                wire:click="subscribe({{ $package->id }})">

                                <x-heroicon-o-x-mark class="mr-1.5 h-4 w-4" />

                                Remove Insurance

                            </x-filament::button>

                        @endif

                    @elseif($isCurrent)

                        <x-filament::button
                            color="success"
                            disabled
                            class="w-full">

                            <x-heroicon-s-check-circle class="mr-1.5 h-4 w-4" />

                            Current Package

                        </x-filament::button>

                    @else

                        <x-filament::button
                            color="primary"
                            class="w-full"
                            wire:click="subscribe({{ $package->id }})">

                            <healthicons-o-truck-driver class="mr-1.5 h-4 w-4" />

                            Subscribe

                        </x-filament::button>

                    @endif

                </div>

            </div>

        @endforeach

    </div>

</x-filament-panels::page>