<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginateOrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query();

        // Shipper restriction: only own orders
        if (Auth::user()->management === 'shipper') {
            $query->where('users_id', Auth::id());
        }

        // Filters
        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->status);
        }

        if ($request->filled('receiver_name')) {
            $query->where('receiver_name', 'like', '%'.$request->receiver_name.'%');
        }

        if ($request->filled('receiver_mobile')) {
            $query->where(function ($q) use ($request) {
                $q->where('receiver_mobile_1', 'like', '%'.$request->receiver_mobile.'%')
                    ->orWhere('receiver_mobile_2', 'like', '%'.$request->receiver_mobile.'%');
            });
        }

        if ($request->filled('users_id') && Auth::user()->management === 'admin') {
            $query->where('users_id', $request->users_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('waybill_number')) {
            $query->where('waybill_number', 'like', '%'.$request->waybill_number.'%');
        }

        // Sort by created_at (descending by default)
        $query->orderBy('created_at', $request->get('direction', 'desc'));

        // Pagination (default 10)
        $orders = $query->paginate($request->get('per_page', 10));

        // Return paginated collection
        return PaginateOrderResource::collection($orders)->additional([
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }
}
