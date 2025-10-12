<x-filament::page>
<div class="space-y-12"> <!-- increased vertical spacing between sections -->
        
<!-- Banner -->
<div class="rounded-3xl p-6 flex items-center justify-between shadow border border-green-300"
     style="background: linear-gradient(#ffe338);">
    <div class="text-black"> <!-- Force all text inside to be black -->
        <div class="flex items-center mb-3">
            <x-filament::icon icon="heroicon-m-arrow-path-rounded-square" class="w-5 h-5 text-green-600 mr-3" />
            <span class="!text-black font-medium">
                FROM DOOR TO DOOR
            </span>
        </div>

        <h2 class="text-3xl font-bold !text-black">
            Ship Smarter with D2D Express
        </h2>

        <p class="mt-2 !text-black">
            Expand your reach, deliver all around Egypt, and grow your business with our seamless door-to-door shipping solutions.
        </p>
    </div>

    <!-- Right side airplane illustration -->
    <x-filament::icon icon="heroicon-o-rocket-launch" class="hidden md:block w-32 h-32 text-green-300 opacity-30" />
</div>


        <!-- Integrations Section -->
        <div class="space-y-4 pt-4 border-t border-gray-200"> <!-- added top padding + subtle divider -->
            <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                <x-filament::icon icon="heroicon-s-link" class="w-5 h-5 text-green-500 mr-2" />
                Integrations
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6"> <!-- increased gap between cards -->
                @foreach ([ 
                    ['title' => 'Connect your Shopify store', 'desc' => 'Automatically sync your store orders.', 'icon' => 'heroicon-o-puzzle-piece'],
                    ['title' => 'Connect your WooCommerce store', 'desc' => 'Manage your store and shipments easily.', 'icon' => 'heroicon-o-puzzle-piece'],
                    ['title' => 'Connect via API', 'desc' => 'Integrate with any system using APIs.', 'icon' => 'heroicon-o-puzzle-piece'],
                ] as $service)
                    <x-filament::card>
                        <div class="flex items-start space-x-3">
                            <x-filament::icon :icon="$service['icon']" class="w-6 h-6 text-green-500" />
                            <div>
                                <h4 class="font-semibold text-gray-800">{{ $service['title'] }}</h4>
                                <p class="text-gray-500 text-sm">{{ $service['desc'] }}</p>
                            </div>
                        </div>
                    </x-filament::card>
                @endforeach
            </div>
        </div>
  <!-- Finance Section -->
<div class="space-y-4 pt-4 border-t border-gray-200">
    <h3 class="text-xl font-semibold text-gray-800 flex items-center">
        <x-filament::icon icon="heroicon-o-currency-dollar" class="w-5 h-5 text-green-500 mr-2" />
        Finances
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> <!-- increased gap -->
        @foreach ([ 
            ['title' => 'Daily profit transfers', 'desc' => 'Receive your cash collections daily.', 'icon' => 'heroicon-o-banknotes'],
            ['title' => 'Financial Reports', 'desc' => 'ALL your collections saved in our system.', 'icon' => 'heroicon-o-chart-bar'],
        ] as $finance)
            <x-filament::card>
                <div class="flex items-start space-x-3">
                    <x-filament::icon :icon="$finance['icon']" class="w-6 h-6 text-green-500" />
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ $finance['title'] }}</h4>
                        <p class="text-gray-500 text-sm">{{ $finance['desc'] }}</p>
                    </div>
                </div>
            </x-filament::card>
        @endforeach
    </div>
</div>

<!-- Management Section -->
<div class="space-y-4 pt-4 border-t border-gray-200">
    <h3 class="text-xl font-semibold text-gray-800 flex items-center">
        <x-filament::icon icon="heroicon-c-cog-6-tooth" class="w-5 h-5 text-green-500 mr-2" />
        Management
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6"> <!-- increased gap -->
        @foreach ([ 
            ['title' => 'Store your products', 'desc' => 'Store, pack and shipping made easily.', 'icon' => 'heroicon-c-building-storefront'],
            ['title' => 'Shipment Insurance', 'desc' => 'Insure your products to apply compensations for any lost or damaged shipments.', 'icon' => 'heroicon-o-check-badge'],
            ['title' => 'Customer Caring', 'desc' => 'Dedicated support from your account manager.', 'icon' => 'heroicon-o-user-group'],
        ] as $service)
            <x-filament::card>
                <div class="flex items-start space-x-3">
                    <x-filament::icon :icon="$service['icon']" class="w-6 h-6 text-green-500" />
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ $service['title'] }}</h4>
                        <p class="text-gray-500 text-sm">{{ $service['desc'] }}</p>
                    </div>
                </div>
            </x-filament::card>
        @endforeach
    </div>
</div>

    </div>
</x-filament::page>
