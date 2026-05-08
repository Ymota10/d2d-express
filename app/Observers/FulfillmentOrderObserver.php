<?php

namespace App\Observers;

use App\Models\FulfillmentOrder;
use App\Models\WarehouseBillingEvent;
use App\Models\WarehouseStock;
use Carbon\Carbon;

class FulfillmentOrderObserver
{
    public function updated(FulfillmentOrder $order): void
    {
        // Only trigger when status becomes delivered
        if (! $order->isDirty('status') || $order->status !== 'success_delivery') {
            return;
        }

        // 🔻 1. Deduct stock
        foreach ($order->items as $item) {
            $stock = WarehouseStock::where('warehouse_item_id', $item->warehouse_item_id)->first();

            if ($stock) {
                $stock->decrement('quantity', $item->quantity);
            }
        }

        // 🔻 2. Billing logic
        $subscription = $order->subscription;
        $plan = $subscription->plan;

        if ($plan->billing_type === 'per_order') {
            WarehouseBillingEvent::create([
                'user_id' => $order->user_id,
                'fulfillment_order_id' => $order->id,
                'amount' => $plan->order_fee,
                'description' => 'Fulfillment fee for order '.$order->reference,
                'billing_month' => Carbon::now()->format('Y-m'),
            ]);
        }
    }
}
