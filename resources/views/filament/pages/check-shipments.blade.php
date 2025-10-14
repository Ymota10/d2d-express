<x-filament-panels::page>
    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-6">
        Review Scanned Shipments
    </h2>

    <!-- Bulk Delete Button -->
    @if(count($orders) > 0)
        <div class="mb-4">
            <x-filament::button
                color="danger"
                wire:click="bulkDelete"
                wire:loading.attr="disabled"
                wire:target="bulkDelete"
            >
                <span wire:loading.remove wire:target="bulkDelete">Delete All</span>
                <span wire:loading wire:target="bulkDelete">Deleting...</span>
            </x-filament::button>
        </div>
    @endif

    <!-- Orders Table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 overflow-x-auto space-y-4">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">Waybill</th>
                    <th class="px-4 py-2 text-left">Order ID</th>
                    <th class="px-4 py-2 text-left">Consignee</th>
                    <th class="px-4 py-2 text-left">Shipper Name</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Area</th>
                    <th class="px-4 py-2 text-left">COD</th>
                    <th class="px-4 py-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($orders as $order)
                    <tr class="bg-transparent">
                        <td class="px-4 py-2">{{ $order->waybill_number }}</td>
                        <td class="px-4 py-2">{{ $order->order_id ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $order->receiver_name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $order->user->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $order->status) }}</td>
                        <td class="px-4 py-2">{{ $order->area->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ number_format($order->cod_amount, 2) }}</td>
                        <td class="px-4 py-2">
                            <x-filament::button color="danger" wire:click="remove({{ $order->id }})">
                                Remove
                            </x-filament::button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-gray-500 dark:text-gray-400 py-4">
                            No shipments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
