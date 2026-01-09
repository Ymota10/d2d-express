<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderAttempt;
use Illuminate\Support\Carbon;

class OrderObserver
{
    public function updated(Order $order): void
    {
        // =================================
        // 1️⃣ Out for delivery → create attempt
        // =================================
        if ($order->isDirty('status') && $order->status === 'out_for_delivery') {

            $lastAttempt = $order->attempts()->max('attempt_number') ?? 0;

            OrderAttempt::create([
                'order_id' => $order->id,
                'attempt_number' => $lastAttempt + 1,
                'status' => 'out_for_delivery',
                'attempted_at' => Carbon::now(),
            ]);
        }

        // =================================
        // 2️⃣ Partial return → clone order
        // =================================
        if ($order->isDirty('status') && $order->status === 'partial_return') {

            $newOrder = $order->replicate();

            $newOrder->status = 'partial_return_2';
            $newOrder->created_at = now();
            $newOrder->updated_at = now();

            $newOrder->save();
        }
    }
}
