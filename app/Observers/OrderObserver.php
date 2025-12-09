<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderAttempt;
use App\Services\WhatsAppService;
use Illuminate\Support\Carbon;

class OrderObserver
{
    public function updated(Order $order)
    {
        // ✅ Run ONLY when status changes to "out_for_delivery"
        if (! $order->isDirty('status')) {
            return;
        }

        if ($order->status !== 'out_for_delivery') {
            return;
        }

        // ✅ Get last attempt number
        $lastAttempt = $order->attempts()->max('attempt_number') ?? 0;

        // ✅ Create new attempt
        OrderAttempt::create([
            'order_id' => $order->id,
            'attempt_number' => $lastAttempt + 1,
            'status' => 'out_for_delivery',
            'attempted_at' => Carbon::now(),
        ]);

        // // ✅ Send WhatsApp ETA Message
        // (new WhatsAppService)->sendEtaMessage($order);
    }
}
