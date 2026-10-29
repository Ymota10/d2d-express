<?php

namespace App\Services;

use App\Models\Order;

class UndeliveredReasonAssigner
{
    /**
     * Assign a reason when an order newly becomes undelivered.
     *
     * Probability:
     * - 70%: refused_shipment
     * - 30%: Randomly choose one of the other four reasons.
     *
     * Existing undelivered orders and their reasons remain unchanged.
     */
    public function assign(Order $order, string $oldStatus, string $newStatus): void
    {
        // Only assign a reason when the order transitions to undelivered.
        if (
            $newStatus !== 'undelivered'
            || $oldStatus === 'undelivered'
        ) {
            return;
        }

        if (random_int(1, 100) <= 70) {
            $order->undelivered_reason = 'refused_shipment';

            return;
        }

        $otherReasons = [
            'refused_payment',
            'no_answer',
            'wrong_location',
            'consignee_escaped',
        ];

        $order->undelivered_reason = $otherReasons[array_rand($otherReasons)];
    }
}
