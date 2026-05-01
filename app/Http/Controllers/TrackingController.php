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

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    public function index()
    {
        return view('tracking.index');
    }
}
