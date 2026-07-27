<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackExpressWebhookController extends Controller
{
    public function status(Request $request)
    {
        Log::info('TrackExpress Webhook Received', $request->all());

        $shipments = $request->input('response');

        if (! $shipments) {
            $shipments = [$request->all()];
        }

        if (empty($shipments)) {

            Log::warning('TrackExpress Invalid Payload', $request->all());

            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook payload',
            ], 400);
        }

        $updated = 0;
        $notFound = [];

        foreach ($shipments as $shipment) {

            Log::info('Processing Shipment', $shipment);

            $trackWaybill = $shipment['waybill'] ?? null;
            $d2dWaybill = $shipment['order_id'] ?? null;

            Log::info('Lookup Data', [
                'track_waybill' => $trackWaybill,
                'd2d_waybill' => $d2dWaybill,
            ]);

            if (! $trackWaybill) {

                Log::warning('Shipment skipped because waybill missing', $shipment);

                continue;
            }

            $order = Order::where('waybill_number', $d2dWaybill)->first();

            Log::info('Order Lookup Result', [
                'searched_waybill_number' => $d2dWaybill,
                'found' => (bool) $order,
                'order_id' => $order?->id,
            ]);

            if (! $order) {

                Log::warning('Order Not Found', [
                    'track_waybill' => $trackWaybill,
                    'd2d_waybill' => $d2dWaybill,
                ]);

                $notFound[] = $d2dWaybill;

                continue;
            }

            $statusId = (int) ($shipment['status_id'] ?? 0);

            $mappedStatus = $this->mapStatus($statusId);

            Log::info('Status Mapping', [
                'status_id' => $statusId,
                'mapped_status' => $mappedStatus,
            ]);

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

                    default => 'refused_shipment',
                };

                $order->undelivered_reason = $mappedReason;

                Log::info('Undelivered Reason Mapped', [
                    'original_reason' => $reason,
                    'mapped_reason' => $mappedReason,
                ]);
            }

            if (
                $statusId === 6 &&
                ! empty($shipment['scheduled'])
            ) {

                $order->time_scheduled_at = Carbon::parse(
                    $shipment['scheduled']
                );

                Log::info('Scheduled Date Saved', [
                    'scheduled' => $shipment['scheduled'],
                ]);
            }

            $order->save();

            if ($order->status === 'warehouse_received') {
                $this->updateShopifyFulfillment($order);
            }

            $this->updateShopifyStatuses($order);

            Log::info('Order Updated Successfully', [
                'order_id' => $order->id,
                'waybill_number' => $order->waybill_number,
                'new_status' => $order->status,
            ]);

            $updated++;
        }

        Log::info('TrackExpress Processing Complete', [
            'updated' => $updated,
            'not_found' => $notFound,
        ]);

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'not_found' => $notFound,
        ]);
    }

    private function updateShopifyFulfillment(Order $order): void
    {
        $user = User::find($order->users_id);

        if (! $user || ! $user->shop_id) {
            Log::warning('Cannot fulfill Shopify order. Shop not found.', [
                'order_id' => $order->id,
            ]);

            return;
        }

        try {

            $url = config('services.shopify_internal.url');
            $key = config('services.shopify_internal.key');

            Http::withHeaders([
                'x-internal-key' => $key,
            ])->post($url.'/internal/shopify/fulfill-orders', [

                'shop' => $user->shop_id,

                'orders' => [[
                    'shopifyOrderId' => $order->order_id,
                    'trackingNumber' => $order->waybill_number,
                    'trackingCompany' => 'D2D Express',
                    'trackingUrl' => 'https://www.d2d-dashboard.com/admin/track?waybill='.$order->waybill_number,
                    'notifyCustomer' => true,
                ]],

            ]);

        } catch (\Throwable $e) {

            Log::error('Shopify fulfillment failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function updateShopifyStatuses(Order $order): void
    {
        $user = User::find($order->users_id);

        if (! $user || ! $user->shop_id) {
            return;
        }

        $status = null;
        $message = null;

        switch ($order->status) {

            case 'out_for_delivery':
                $status = 'OUT_FOR_DELIVERY';
                $message = 'Order is out for delivery by D2DExpress';
                break;

            case 'success_delivery':
                $status = 'DELIVERED';
                $message = 'Order delivered by D2DExpress';
                break;

            case 'failed_attempt':
                $status = 'FAILURE';
                $message = 'Delivery attempt failed';
                break;

            case 'time_scheduled':
                $status = 'IN_TRANSIT';
                $message = 'Order is in transit';
                break;

            case 'returned_to_warehouse':
                $status = 'IN_TRANSIT';
                $message = 'Order returned to warehouse and is still in transit';
                break;

            default:
                return;
        }

        try {

            $url = config('services.shopify_internal.url');
            $key = config('services.shopify_internal.key');

            $response = Http::withHeaders([
                'x-internal-key' => $key,
            ])->post($url.'/internal/shopify/update-fulfillment', [

                'shop' => $user->shop_id,

                'orders' => [[
                    'shopifyOrderId' => $order->order_id,
                    'status' => $status,
                    'message' => $message,
                ]],

            ]);

            Log::info('Shopify fulfillment status updated', [
                'order_id' => $order->id,
                'shopify_status' => $status,
                'response' => $response->json(),
            ]);

        } catch (\Throwable $e) {

            Log::error('Shopify fulfillment status update failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function mapStatus($statusId)
    {
        return match ($statusId) {

            1 => 'pickup_request',
            3 => 'warehouse_received',
            4 => 'out_for_delivery',
            5 => 'success_delivery',
            6 => 'time_scheduled',
            7 => 'returned_to_warehouse',
            13 => 'failed_attempt',
            14 => 'returned_to_shipper',
            18 => 'partial_return',
            19 => 'undelivered',
            20 => 'returned_and_cost_paid',

            default => null,
        };
    }
}
