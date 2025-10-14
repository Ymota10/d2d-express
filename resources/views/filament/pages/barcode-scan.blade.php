<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Input Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
            <form wire:submit.prevent="search" class="space-y-4">
                {{ $this->form }}

                @if(count($duplicates))
                    <div class="bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-200 p-2 rounded text-sm">
                        <strong>Warning:</strong> Duplicate waybills found:
                        {{ implode(', ', $duplicates) }}
                    </div>
                @endif

                <x-filament::button
                    type="submit"
                    color="success"
                    size="sm"
                    wire:loading.attr="disabled"
                    wire:target="search"
                    class="w-full"
                >
                    <span wire:loading.remove wire:target="search">Search Orders</span>
                    <span wire:loading wire:target="search">Searching...</span>
                </x-filament::button>
            </form>
        </div>

        <!-- Results Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Scanned Orders</h3>

            @if(count($orders))
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 dark:border-gray-700 rounded-lg">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Waybill</th>
                                <th class="px-4 py-2 text-left">Order ID</th>
                                <th class="px-4 py-2 text-left">Consignee</th>
                                <th class="px-4 py-2 text-left">Shipper Name</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Area</th>
                                <th class="px-4 py-2 text-left">COD</th>
                                <th class="px-4 py-2 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr class="{{ in_array($order['waybill_number'], $duplicates) ? 'bg-red-100 dark:bg-red-700' : 'bg-green-100 dark:bg-green-700' }}">
                                    <td class="px-4 py-2">{{ $order['waybill_number'] }}</td>
                                    <td class="px-4 py-2">{{ $order['order_id'] }}</td>
                                    <td class="px-4 py-2">{{ $order['receiver_name'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">{{ $order['user']['name'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">{{ $order['status'] }}</td>
                                    <td class="px-4 py-2">{{ $order['area']['name'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">{{ number_format($order['cod_amount'], 2) }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <x-filament::button
                                            color="danger"
                                            size="sm"
                                            wire:click="remove('{{ $order['id'] }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="remove('{{ $order['id'] }}')"
                                        >
                                            <span wire:loading.remove wire:target="remove('{{ $order['id'] }}')">Remove</span>
                                            <span wire:loading wire:target="remove('{{ $order['id'] }}')">Removing...</span>
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <x-filament::button
                        color="primary"
                        size="lg"
                        wire:click="submitScannedOrders"
                        wire:loading.attr="disabled"
                        wire:target="submitScannedOrders"
                        class="w-full"
                    >
                        <span wire:loading.remove wire:target="submitScannedOrders">Submit</span>
                        <span wire:loading wire:target="submitScannedOrders">Loading...</span>
                    </x-filament::button>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">No orders found yet.</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
