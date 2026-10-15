<?php

namespace App\Http\Controllers;

use App\Models\Order;

class TrackingController extends Controller
{
    public function search($waybill)
    {
        $order = Order::where('waybill_number', $waybill)->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Tracking number not found',
            ]);
        }

        $trackingUrl = $order->courier_tracking_url;
        $trackingNumber = $order->courier_tracking_number;

        // Replace Flextock's {0} placeholder with the actual courier tracking number.
        if (
            $trackingUrl &&
            $trackingNumber &&
            str_contains($trackingUrl, '{0}')
        ) {
            $trackingUrl = str_replace(
                '{0}',
                rawurlencode($trackingNumber),
                $trackingUrl
            );
        }

        // Only return valid HTTPS tracking links.
        if (
            ! $trackingUrl ||
            ! filter_var($trackingUrl, FILTER_VALIDATE_URL) ||
            strtolower((string) parse_url($trackingUrl, PHP_URL_SCHEME)) !== 'https'
        ) {
            $trackingUrl = null;
        }

        return response()->json([
            'success' => true,
            'order' => $order,
            'courier_tracking' => [
                'courier_name' => $order->flextock_courier_name,
                'tracking_number' => $trackingNumber,
                'tracking_url' => $trackingUrl,
            ],
        ]);
    }

    public function index()
    {
        return view('tracking.index');
    }
}
