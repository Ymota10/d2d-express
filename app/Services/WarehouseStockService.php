<?php

namespace App\Services;

use App\Models\WarehouseStock;
use App\Models\WarehouseStockMovement;
use Illuminate\Validation\ValidationException;

class WarehouseStockService
{
    public function out(
        int $warehouseItemId,
        int $quantity,
        ?int $fulfillmentOrderId = null
    ): void {
        $stock = WarehouseStock::where('warehouse_item_id', $warehouseItemId)->lockForUpdate()->first();

        if (! $stock || $stock->quantity < $quantity) {
            throw ValidationException::withMessages([
                'stock' => 'Insufficient stock for this item.',
            ]);
        }

        WarehouseStockMovement::create([
            'warehouse_item_id' => $warehouseItemId,
            'fulfillment_order_id' => $fulfillmentOrderId,
            'type' => 'out',
            'quantity' => $quantity,
        ]);

        $stock->decrement('quantity', $quantity);
    }

    public function in(int $warehouseItemId, int $quantity, ?int $fulfillmentOrderId = null)
    {
        $stock = WarehouseStock::firstOrCreate(
            ['warehouse_item_id' => $warehouseItemId],
            ['quantity' => 0]
        );

        $stock->quantity += $quantity;
        $stock->save();

        // Optional: create a stock movement entry
        WarehouseStockMovement::create([
            'warehouse_item_id' => $warehouseItemId,
            'fulfillment_order_id' => $fulfillmentOrderId,
            'type' => 'in',
            'quantity' => $quantity,
        ]);
    }
}
