<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\WhatsAppService;

class OrderObserver
{
    public function updated(Order $order)
    {
        // Check if status changed to 'out_for_delivery'
        if ($order->isDirty('status') && $order->status === 'out_for_delivery') {
            // Send WhatsApp ETA message
            (new WhatsAppService)->sendEtaMessage($order);
        }
    }
}
