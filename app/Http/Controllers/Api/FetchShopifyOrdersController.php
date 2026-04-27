<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class FetchShopifyOrdersController extends Controller
{
    public function fetch(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'required',
        ]);

        // get matching orders
        $orders = Order::whereIn('order_id', $request->order_ids)
            ->post(['id', 'order_id']);

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }
}
