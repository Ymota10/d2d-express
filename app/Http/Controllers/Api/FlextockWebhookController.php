<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlextockWebhookController extends Controller
{
    public function status(Request $request)
    {
        Log::info('Flextock Status Webhook Received', [
            'payload' => $request->all(),
        ]);

        $orderCode = $request->input('order_code');
        $flextockStatus = $request->input('order_status');
        $subStatus = $request->input('sub_status');

        /*
        |--------------------------------------------------------------------------
        | VALIDATE PAYLOAD
        |--------------------------------------------------------------------------
        */

        if (! $orderCode) {
            return response()->json([
                'success' => false,
                'message' => 'order_code is required',
            ], 400);
        }

        if (! $flextockStatus) {
            return response()->json([
                'success' => false,
                'message' => 'order_status is required',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | FIND OUR ORDER
        |--------------------------------------------------------------------------
        |
        | When creating the Flextock order we send:
        |
        | order_code => $order->waybill_number
        |
        */

        $order = Order::where('waybill_number', $orderCode)->first();

        if (! $order) {

            Log::warning('Flextock Order Not Found', [
                'order_code' => $orderCode,
                'order_status' => $flextockStatus,
                'sub_status' => $subStatus,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'order_code' => $orderCode,
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | MAP FLEXTOCK STATUS
        |--------------------------------------------------------------------------
        */

        $mappedStatus = $this->mapStatus(
            $flextockStatus,
            $subStatus
        );

        Log::info('Flextock Status Mapping', [
            'order_id' => $order->id,
            'order_code' => $orderCode,
            'flextock_status' => $flextockStatus,
            'sub_status' => $subStatus,
            'mapped_status' => $mappedStatus,
            'old_status' => $order->status,
        ]);

        /*
        |--------------------------------------------------------------------------
        | DAMAGED / UNKNOWN / UNMAPPED STATUS
        |--------------------------------------------------------------------------
        |
        | "damaged" intentionally has no D2D status mapping.
        |
        | We don't change the order status in this case.
        |
        */

        if (! $mappedStatus) {

            Log::warning('Flextock Status Has No Mapping - Order Not Changed', [
                'order_id' => $order->id,
                'order_code' => $orderCode,
                'flextock_status' => $flextockStatus,
                'sub_status' => $subStatus,
                'current_status' => $order->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Flextock status received but no D2D status mapping exists. Order was not changed.',
                'order_id' => $order->id,
                'order_code' => $orderCode,
                'flextock_status' => $flextockStatus,
                'sub_status' => $subStatus,
                'mapped_status' => null,
                'current_status' => $order->status,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE ORDER STATUS
        |--------------------------------------------------------------------------
        */

        $oldStatus = $order->status;

        $order->status = $mappedStatus;

        $order->save();

        Log::info('Flextock Order Status Updated Successfully', [
            'order_id' => $order->id,
            'order_code' => $orderCode,
            'old_status' => $oldStatus,
            'new_status' => $order->status,
        ]);

        /*
        |--------------------------------------------------------------------------
        | SHOPIFY FULFILLMENT
        |--------------------------------------------------------------------------
        |
        | Same behavior as TrackExpress:
        |
        | warehouse_received
        | -> create Shopify fulfillment
        | -> send tracking number
        | -> send tracking URL
        |
        */

        if ($order->status === 'warehouse_received') {
            $this->updateShopifyFulfillment($order);
        }

        /*
        |--------------------------------------------------------------------------
        | SHOPIFY STATUS
        |--------------------------------------------------------------------------
        |
        | Send the mapped D2D status to Shopify where applicable.
        |
        */

        $this->updateShopifyStatuses($order);

        Log::info('Flextock Processing Complete', [
            'order_id' => $order->id,
            'order_code' => $orderCode,
            'flextock_status' => $flextockStatus,
            'sub_status' => $subStatus,
            'old_status' => $oldStatus,
            'new_status' => $order->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'order_id' => $order->id,
            'order_code' => $orderCode,
            'flextock_status' => $flextockStatus,
            'sub_status' => $subStatus,
            'mapped_status' => $mappedStatus,
            'old_status' => $oldStatus,
            'new_status' => $order->status,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FLEXTOCK -> D2D STATUS MAPPING
    |--------------------------------------------------------------------------
    |
    | Flextock gives us:
    |
    | order_status
    | sub_status
    |
    | We convert those strings into our database statuses.
    |
    */

    private function mapStatus(
        string $status,
        ?string $subStatus = null
    ): ?string {

        $status = strtolower(trim($status));

        $subStatus = $subStatus
            ? strtolower(trim($subStatus))
            : null;

        return match ($status) {

            /*
            |--------------------------------------------------------------------------
            | PENDING
            |--------------------------------------------------------------------------
            */

            'pending' => 'pickup_request',

            /*
            |--------------------------------------------------------------------------
            | ON HOLD
            |--------------------------------------------------------------------------
            */

            'on hold' => match ($subStatus) {

                'no answer' => 'failed_attempt',

                default => 'pickup_request',
            },

            /*
            |--------------------------------------------------------------------------
            | CANCELED
            |--------------------------------------------------------------------------
            */

            'canceled' => 'undelivered',

            /*
            |--------------------------------------------------------------------------
            | READY
            |--------------------------------------------------------------------------
            */

            'ready' => match ($subStatus) {

                'rescheduled' => 'time_scheduled',

                default => 'warehouse_received',
            },

            /*
            |--------------------------------------------------------------------------
            | PROCESSING
            |--------------------------------------------------------------------------
            */

            'processing' => match ($subStatus) {

                'rescheduled' => 'time_scheduled',

                'cancellation rejected' => 'time_scheduled',

                default => 'warehouse_received',
            },

            /*
            |--------------------------------------------------------------------------
            | FULFILLED
            |--------------------------------------------------------------------------
            */

            'fulfilled' => 'warehouse_received',

            /*
            |--------------------------------------------------------------------------
            | IN TRANSIT
            |--------------------------------------------------------------------------
            */

            'in transit' => match ($subStatus) {

                'pending out for delivery' => 'out_for_delivery',

                'out for delivery' => 'out_for_delivery',

                'failed attempt' => 'failed_attempt',

                'lost' => 'lost',

                /*
                 * Damaged intentionally has NO mapping.
                 * Returning null means the order status
                 * will remain unchanged.
                 */
                'damaged' => null,

                default => null,
            },

            /*
            |--------------------------------------------------------------------------
            | DELIVERED
            |--------------------------------------------------------------------------
            */

            'delivered' => 'success_delivery',

            /*
            |--------------------------------------------------------------------------
            | PICK UP
            |--------------------------------------------------------------------------
            */

            'pick up' => 'pickup_request',

            /*
            |--------------------------------------------------------------------------
            | RETURNING
            |--------------------------------------------------------------------------
            */

            'returning' => match ($subStatus) {

                'rto' => 'returned_to_warehouse',

                default => null,
            },

            /*
            |--------------------------------------------------------------------------
            | RETURNED TO ORIGIN
            |--------------------------------------------------------------------------
            */

            'returned to origin' => match ($subStatus) {

                'received' => 'returned_to_shipper',

                'stocked' => 'returned_to_shipper',

                'ready for return' => 'returned_to_warehouse',

                default => null,
            },

            /*
            |--------------------------------------------------------------------------
            | LOST
            |--------------------------------------------------------------------------
            */

            'lost' => match ($subStatus) {

                'by courier' => 'lost',

                'by flextock' => 'lost',

                default => 'lost',
            },

            /*
            |--------------------------------------------------------------------------
            | UNKNOWN STATUS
            |--------------------------------------------------------------------------
            */

            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | SHOPIFY FULFILLMENT
    |--------------------------------------------------------------------------
    |
    | Creates the fulfillment in Shopify and sends:
    |
    | - Shopify order ID
    | - D2D waybill
    | - D2D Express as tracking company
    | - D2D tracking URL
    |
    */

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

            $response = Http::withHeaders([
                'x-internal-key' => $key,
            ])->post(
                $url.'/internal/shopify/fulfill-orders',
                [
                    'shop' => $user->shop_id,

                    'orders' => [[
                        'shopifyOrderId' => $order->order_id,

                        'trackingNumber' => $order->waybill_number,

                        'trackingCompany' => 'LYNK',

                        'trackingUrl' => 'https://www.d2d-dashboard.com/admin/track?waybill='
                            .$order->waybill_number,

                        'notifyCustomer' => true,
                    ]],
                ]
            );

            Log::info('Shopify fulfillment created from Flextock webhook', [
                'order_id' => $order->id,
                'shop_id' => $user->shop_id,
                'tracking_number' => $order->waybill_number,
                'response' => $response->json(),
            ]);

        } catch (\Throwable $e) {

            Log::error('Shopify fulfillment failed from Flextock webhook', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOPIFY STATUS UPDATE
    |--------------------------------------------------------------------------
    |
    | Same mapping currently used by TrackExpressWebhookController.
    |
    */

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

                $message =
                    'Order returned to warehouse and is still in transit';

                break;

            default:

                return;
        }

        try {

            $url = config('services.shopify_internal.url');
            $key = config('services.shopify_internal.key');

            $response = Http::withHeaders([
                'x-internal-key' => $key,
            ])->post(
                $url.'/internal/shopify/update-fulfillment',
                [
                    'shop' => $user->shop_id,

                    'orders' => [[
                        'shopifyOrderId' => $order->order_id,

                        'status' => $status,

                        'message' => $message,
                    ]],
                ]
            );

            Log::info('Shopify fulfillment status updated from Flextock', [
                'order_id' => $order->id,
                'shopify_status' => $status,
                'message' => $message,
                'response' => $response->json(),
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Shopify fulfillment status update failed from Flextock',
                [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]
            );
        }
    }
}
