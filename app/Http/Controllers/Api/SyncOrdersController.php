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
                | ITEMS
                |------------------------------------------
                */
                $orderItems = collect($shopifyOrder['orderItems'] ?? []);

                $items = $orderItems->pluck('name')->implode(', ');
                $quantity = $orderItems->sum('count');
                $size = $orderItems->first()['size'] ?? null;

                /*
                |------------------------------------------
                | CITY RESOLUTION
                |------------------------------------------
                */
                $provinceName = $shopifyOrder['shippingDetails']['province'] ?? null;
                $addressText = $shopifyOrder['shippingDetails']['address1'] ?? '';

                $city = City::all()->first(function ($c) use ($provinceName) {
                    return levenshtein(strtolower($c->name), strtolower($provinceName)) <= 3;
                });

                /*
                |------------------------------------------
                | AREA (BEST MATCH USING LEVENSHTEIN)
                |------------------------------------------
                */

                $finalArea = null;

                if ($city) {

                    $areas = Area::where('city_id', $city->id)->get();

                    // 1. CLEAN + TOKENIZE ADDRESS
                    $address = strtolower($shopifyOrder['shippingDetails']['address1'] ?? '');
                    $addressTokens = array_filter(
                        preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/', ' ', $address))
                    );

                    $bestScore = -1;

                    foreach ($areas as $area) {

                        $areaName = strtolower($area->name);

                        // 2. TOKENIZE AREA NAME
                        $areaTokens = array_filter(
                            preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/', ' ', $areaName))
                        );

                        $score = 0;

                        /*
                        ---------------------------------------
                        1. EXACT PHRASE MATCH (VERY STRONG)
                        ---------------------------------------
                        */
                        if (str_contains($address, $areaName)) {
                            $score += 100;
                        }

                        /*
                        ---------------------------------------
                        2. TOKEN OVERLAP MATCHING (CORE LOGIC)
                        ---------------------------------------
                        */
                        foreach ($areaTokens as $token) {
                            if (strlen($token) < 3) {
                                continue;
                            }

                            if (in_array($token, $addressTokens)) {
                                $score += 25;
                            }

                            // partial match (important for typos)
                            foreach ($addressTokens as $addrToken) {
                                if (levenshtein($token, $addrToken) <= 2) {
                                    $score += 15;
                                }
                            }
                        }

                        /*
                        ---------------------------------------
                        3. CITY BOOST
                        ---------------------------------------
                        */
                        if (str_contains($address, strtolower($city->name))) {
                            $score += 30;
                        }

                        /*
                        ---------------------------------------
                        4. BEST PICK
                        ---------------------------------------
                        */
                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $finalArea = $area;
                        }
                    }

                    // fallback safety
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

                    'receiver_mobile_1' => $shopifyOrder['customerDetails']['phone'] ?? null,
                    'receiver_mobile_2' => $shopifyOrder['shippingDetails']['phone'] ?? null,
                    'receiver_address' => $shopifyOrder['shippingDetails']['address1'] ?? null,

                    'cod_amount' => $shopifyOrder['totalPrice'] ?? 0,

                    'item_name' => $items,
                    'quantity' => $quantity,
                    'size' => $size,

                    'order_id' => $shopifyOrder['id'],
                    'external_order_id' => $shopifyOrder['id'],

                    'notes' => json_encode($shopifyOrder['paymentGatewayNames'] ?? []),

                    'service_type' => $this->mapOrderType(
                        $shopifyOrder['orderType'] ?? 'delivery'
                    ),

                    'status' => 'pickup_request',

                    /*
                    |------------------------------------------
                    | SHIPPING COST
                    |------------------------------------------
                    */
                    'city_id' => $city?->id ?? 1,
                    'area_id' => $finalArea?->id ?? 1,
                    'delivery_cost' => $finalArea?->delivery_cost ?? 85,

                    'source' => 'shopify',

                    'external_payload' => json_encode($shopifyOrder),
                ]);

                $createdOrders[] = [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'users_id' => $order->users_id,
                    'delivery_cost' => $order->delivery_cost,
                    'open_package' => $order->open_package,
                    'open_package_fee' => $order->open_package_fee,
                    'quantity' => $order->quantity,
                    'size' => $order->size,
                ];
            }
        });

        return response()->json([
            'status' => true,
            'shop_id' => $request->shop_id,
            'users_id' => $user->id,
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
