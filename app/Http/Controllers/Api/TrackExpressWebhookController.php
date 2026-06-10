<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackExpressWebhookController extends Controller
{
    public function status(Request $request)
    {
        Log::info('TrackExpress Webhook', $request->all());

        $shipments = $request->input('response', []);

        if (empty($shipments)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook payload',
            ], 400);
        }

        $updated = 0;
        $notFound = [];

        foreach ($shipments as $shipment) {

            $waybill = $shipment['waybill'] ?? null;

            if (! $waybill) {
                continue;
            }

            $order = Order::where('waybill_number', $waybill)->first();

            if (! $order) {
                $notFound[] = $waybill;

                continue;
            }

            $statusId = (int) ($shipment['status_id'] ?? 0);

            $mappedStatus = $this->mapStatus($statusId);

            if ($mappedStatus) {
                $order->status = $mappedStatus;
            }

            if ($statusId === 13) {

                $reason = strtolower($shipment['reason'] ?? '');

                $mappedReason = match (true) {

                    str_contains($reason, 'no answer') => 'no_answer',

                    str_contains($reason, 'refuse payment') ||
                    str_contains($reason, 'refused payment') => 'refused_payment',

                    str_contains($reason, 'wrong location') => 'wrong_location',

                    str_contains($reason, 'refused shipment') => 'refused_shipment',

                    str_contains($reason, 'consignee') &&
                    str_contains($reason, 'escaped') => 'consignee_escaped',

                    default => 'refused_shipment', // ✅ fallback
                };

                $order->undelivered_reason = $mappedReason;
            }

            if (
                $statusId === 6 &&
                ! empty($shipment['scheduled'])
            ) {
                $order->time_scheduled_at = Carbon::parse($shipment['scheduled']);
            }

            $order->save();
            $updated++;
        }

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'not_found' => $notFound,
        ]);
    }

    private function mapStatus($statusId)
    {
        return match ($statusId) {

            // Pickup Request
            1 => 'pickup_request',

            // Warehouse Received
            3 => 'warehouse_received',

            // Out For Delivery
            4 => 'out_for_delivery',

            // Successful Delivery
            5 => 'success_delivery',

            // Time Scheduled
            6 => 'time_scheduled',

            // Returned to Warehouse
            7 => 'returned_to_warehouse',

            // Undelivered
            13 => 'undelivered',

            // Returned to Shipper
            14 => 'returned_to_shipper',

            // Partial Delivery
            18 => 'partial_return',

            // Returned and Cost Paid
            20 => 'returned_and_cost_paid',

            default => null,
        };
    }
}
