<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncOrdersController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|string',
            'orders' => 'required|array',
        ]);

        $user = User::where('shop_id', $request->shop_id)->first();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        $createdOrders = [];

        DB::transaction(function () use ($request, $user, &$createdOrders) {

            foreach ($request->orders as $shopifyOrder) {

                if (! ($shopifyOrder['valid'] ?? true)) {
                    continue;
                }

                /*
                |------------------------------------------
                | ITEMS (FIXED FOR SHOPIFY STRUCTURE)
                |------------------------------------------
                */
                $orderItems = collect($shopifyOrder['orderItems'] ?? []);

                // No product name anymore → fallback to variantId
                $items = $orderItems->map(function ($item) {
                    return $item['variantId'] ?? 'unknown-item';
                })->implode(', ');

                $quantity = $orderItems->sum('count');

                /*
                |------------------------------------------
                | SIZE (optional if you later add metafields)
                |------------------------------------------
                */
                $size = null;

                /*
                |------------------------------------------
                | PHONE FIX (IMPORTANT)
                |------------------------------------------
                */
                $phone = $shopifyOrder['customerDetails']['phone']
                    ?? $shopifyOrder['shippingDetails']['phone']
                    ?? null;

                /*
                |------------------------------------------
                | CITY + AREA (KEEP YOUR LOGIC)
                |------------------------------------------
                */
                $provinceName = $shopifyOrder['shippingDetails']['province'] ?? null;
                $addressText = $shopifyOrder['shippingDetails']['address1'] ?? '';

                $city = City::where('name', $provinceName)->first();

                $finalArea = null;

                if ($city) {

                    $areas = Area::where('city_id', $city->id)->get();

                    $address = strtolower($addressText);
                    $addressTokens = array_filter(
                        preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/', ' ', $address))
                    );

                    $bestScore = -1;

                    foreach ($areas as $area) {

                        $areaName = strtolower($area->name);

                        $areaTokens = array_filter(
                            preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/', ' ', $areaName))
                        );

                        $score = 0;

                        if (str_contains($address, $areaName)) {
                            $score += 100;
                        }

                        foreach ($areaTokens as $token) {
                            if (strlen($token) < 3) {
                                continue;
                            }

                            if (in_array($token, $addressTokens)) {
                                $score += 25;
                            }

                            foreach ($addressTokens as $addrToken) {
                                if (levenshtein($token, $addrToken) <= 2) {
                                    $score += 15;
                                }
                            }
                        }

                        if (str_contains($address, strtolower($city->name))) {
                            $score += 30;
                        }

                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $finalArea = $area;
                        }
                    }

                    if (! $finalArea) {
                        $finalArea = $areas->first();
                    }
                }

                /*
                |------------------------------------------
                | OPEN PACKAGE (FROM USER SETTINGS)
                |------------------------------------------
                */
                $openPackage = $user->open_package ?? 'no';
                $openPackageFee = $openPackage === 'yes' ? 7 : 0;

                /*
                |------------------------------------------
                | CREATE ORDER
                |------------------------------------------
                */
                $order = Order::create([
                    'open_package' => $openPackage,
                    'open_package_fee' => $openPackageFee,

                    'waybill_number' => uniqid('WB-'),
                    'users_id' => $user->id,

                    'receiver_name' => ($shopifyOrder['customerDetails']['firstName'] ?? '').' '.
                        ($shopifyOrder['customerDetails']['lastName'] ?? ''),

                    'receiver_mobile_1' => $phone,
                    'receiver_mobile_2' => null,

                    'receiver_address' => $addressText,

                    'cod_amount' => $shopifyOrder['totalPrice'] ?? 0,

                    'item_name' => $items,
                    'quantity' => $quantity,
                    'size' => $size,

                    'order_id' => $shopifyOrder['id'],
                    'external_order_id' => $shopifyOrder['id'],

                    'notes' => json_encode($shopifyOrder['paymentGatewayNames'] ?? []),

                    'service_type' => $this->mapOrderType($shopifyOrder['orderType'] ?? 'delivery'),

                    'status' => 'pickup_request',

                    'city_id' => $city?->id ?? 1,
                    'area_id' => $finalArea?->id ?? 1,
                    'delivery_cost' => $finalArea?->delivery_cost ?? 85,

                    'source' => 'shopify',
                    'external_payload' => json_encode($shopifyOrder),
                ]);

                $createdOrders[] = [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'delivery_cost' => $order->delivery_cost,
                ];
            }
        });

        return response()->json([
            'status' => true,
            'shop_id' => $request->shop_id,
            'created_orders' => $createdOrders,
        ]);
    }

    private function mapOrderType($type)
    {
        return match ($type) {
            'return' => 'refund',
            'exchange' => 'replacement',
            default => 'normal_cod',
        };
    }
}
