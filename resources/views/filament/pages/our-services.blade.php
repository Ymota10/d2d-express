<x-filament::page>
    <div class="space-y-12">

        {{-- Intro --}}
        <div class="max-w-2xl">

            <h1 class="mt-3 text-2xl font-bold text-gray-950 dark:text-white sm:text-3xl">
                Everything You Need to Ship, Store, and Get Paid
            </h1>

            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400 sm:text-base">
                From pickup to doorstep to daily settlement, Lynk covers every step of the delivery
                journey so you can focus on running your business.
            </p>
        </div>

        {{-- Services grid --}}
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">

            @foreach([
                [
                    'title' => 'Nationwide Delivery',
                    'desc' => 'Reliable door-to-door delivery reaching every governorate in Egypt.',
                    'icon' => 'heroicon-o-truck',
                    'points' => ['27 governorates covered', 'Live status updates on every stop'],
                ],
                [
                    'title' => 'Same-Day & Express',
                    'desc' => 'Priority routing for time-sensitive shipments that can\'t wait.',
                    'icon' => 'heroicon-o-bolt',
                    'points' => ['Same-day pickup slots', 'Priority handling at every hub'],
                ],
                [
                    'title' => 'Cash-on-Delivery Collection',
                    'desc' => 'We collect COD on your behalf and settle it back to you daily.',
                    'icon' => 'heroicon-o-banknotes',
                    'points' => ['Daily settlement cycle', 'Full collection transparency'],
                ],
                [
                    'title' => 'Warehousing & Fulfillment',
                    'desc' => 'Store inventory and let us pack and ship orders as they come in.',
                    'icon' => 'heroicon-o-building-storefront',
                    'points' => ['Pick, pack &amp; ship handled for you', 'Real-time stock visibility'],
                ],
                [
                    'title' => 'Returns & Reverse Logistics',
                    'desc' => 'A smooth return path for customers, with minimal handling for you.',
                    'icon' => 'heroicon-o-arrow-uturn-left',
                    'points' => ['Managed pickup for returns', 'Condition checks before restock'],
                ],
                [
                    'title' => 'Shipment Insurance',
                    'desc' => 'Every parcel can be covered against loss or damage in transit.',
                    'icon' => 'heroicon-o-shield-check',
                    'points' => ['Optional coverage per shipment', 'Straightforward claims process'],
                ],
            ] as $service)

                <div class="flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30">

                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB] dark:bg-blue-500/10 dark:text-blue-400">
                        <x-filament::icon :icon="$service['icon']" class="h-6 w-6" />
                    </div>

                    <h3 class="mt-4 text-base font-semibold text-gray-950 dark:text-white">
                        {{ $service['title'] }}
                    </h3>

                    <p class="mt-1.5 text-sm leading-6 text-gray-500 dark:text-gray-400">
                        {{ $service['desc'] }}
                    </p>

                    <ul class="mt-4 space-y-2 border-t border-gray-100 pt-4 dark:border-white/10">
                        @foreach($service['points'] as $point)
                            <li class="flex items-start gap-2 text-xs text-gray-600 dark:text-gray-400">
                                <x-filament::icon icon="heroicon-s-check" class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-[#2563EB] dark:text-blue-400" />
                                {!! $point !!}
                            </li>
                        @endforeach
                    </ul>
                </div>

            @endforeach

        </div>
        

        {{-- Closing note --}}
        <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            Need a service that isn't listed here? Reach out to your account manager to discuss it.
        </p>

    </div>
</x-filament::page>