<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
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
        | VALIDATION
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
        | When creating the Flextock order, we send:
        |
        | order_code => $order->waybill_number
        |
        | Therefore we find our order using waybill_number.
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
        | NO MAPPING
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | If Flextock sends a status that we intentionally do not map,
        | such as:
        |
        | in transit + damaged
        |
        | we DO NOT change our order status.
        |
        */

        if (! $mappedStatus) {

            Log::info('Flextock Status Ignored - No Mapping', [
                'order_id' => $order->id,
                'order_code' => $orderCode,
                'flextock_status' => $flextockStatus,
                'sub_status' => $subStatus,
                'current_status' => $order->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Flextock status received but no mapping was configured. Order status was not changed.',
                'order_id' => $order->id,
                'order_code' => $orderCode,
                'flextock_status' => $flextockStatus,
                'sub_status' => $subStatus,
                'current_status' => $order->status,
                'mapped_status' => null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE ORDER
        |--------------------------------------------------------------------------
        */

        $oldStatus = $order->status;

        $order->status = $mappedStatus;

        $order->save();

        /*
        |--------------------------------------------------------------------------
        | LOG SUCCESS
        |--------------------------------------------------------------------------
        */

        Log::info('Flextock Order Status Updated Successfully', [
            'order_id' => $order->id,
            'order_code' => $orderCode,
            'old_status' => $oldStatus,
            'new_status' => $order->status,
            'flextock_status' => $flextockStatus,
            'sub_status' => $subStatus,
        ]);

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'order_id' => $order->id,
            'order_code' => $orderCode,
            'flextock_status' => $flextockStatus,
            'sub_status' => $subStatus,
            'old_status' => $oldStatus,
            'mapped_status' => $mappedStatus,
            'new_status' => $order->status,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FLEXTOCK → D2D STATUS MAPPING
    |--------------------------------------------------------------------------
    |
    | This mapping follows the agreed mapping table exactly.
    |
    */

    private function mapStatus(
        string $status,
        ?string $subStatus = null
    ): ?string {

        $status = strtolower(trim($status));

        $subStatus = $subStatus !== null
            ? strtolower(trim($subStatus))
            : null;

        return match ($status) {

            /*
            |--------------------------------------------------------------------------
            | PENDING
            |--------------------------------------------------------------------------
            |
            | pending + null
            | pending + confirmed
            |
            */

            'pending' => 'pickup_request',

            /*
            |--------------------------------------------------------------------------
            | ON HOLD
            |--------------------------------------------------------------------------
            |
            | Most on-hold statuses remain pickup_request.
            |
            | EXCEPTION:
            | on hold + no answer => failed_attempt
            |
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
            |
            | ready + null
            |       => warehouse_received
            |
            | ready + rescheduled
            |       => time_scheduled
            |
            */

            'ready' => match ($subStatus) {

                'rescheduled' => 'time_scheduled',

                default => 'warehouse_received',
            },

            /*
            |--------------------------------------------------------------------------
            | PROCESSING
            |--------------------------------------------------------------------------
            |
            | processing + null
            |       => warehouse_received
            |
            | processing + cancellation in progress
            |       => warehouse_received
            |
            | processing + cancellation rejected
            |       => time_scheduled
            |
            | processing + rescheduled
            |       => time_scheduled
            |
            */

            'processing' => match ($subStatus) {

                'cancellation rejected' => 'time_scheduled',

                'rescheduled' => 'time_scheduled',

                'cancellation in progress' => 'warehouse_received',

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
            |
            | pending out for delivery => out_for_delivery
            | out for delivery         => out_for_delivery
            | failed attempt           => failed_attempt
            | lost                     => lost
            | damaged                  => NO MAPPING
            |
            */

            'in transit' => match ($subStatus) {

                'pending out for delivery' => 'out_for_delivery',

                'out for delivery' => 'out_for_delivery',

                'failed attempt' => 'failed_attempt',

                'lost' => 'lost',

                /*
                | IMPORTANT:
                | damaged intentionally has NO mapping.
                | Returning null here means our existing order status
                | will remain unchanged.
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

            'pick up' => 'out_for_delivery',

            /*
            |--------------------------------------------------------------------------
            | RETURNING
            |--------------------------------------------------------------------------
            |
            | returning + rto => returned_to_warehouse
            |
            */

            'returning' => match ($subStatus) {

                'rto' => 'returned_to_warehouse',

                default => null,
            },

            /*
            |--------------------------------------------------------------------------
            | RETURNED TO ORIGIN
            |--------------------------------------------------------------------------
            |
            | received          => returned_to_shipper
            | stocked           => returned_to_shipper
            | ready for return  => returned_to_warehouse
            |
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
            |
            | lost + by courier
            | lost + by flextock
            |
            | Both map to our "lost" status.
            |
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
}
