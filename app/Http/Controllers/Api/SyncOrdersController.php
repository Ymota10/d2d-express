<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\AreaTier1;
use App\Models\AreaTier2;
use App\Models\City;
use App\Models\Order;
use App\Models\ShopifySetting;
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
            'auto' => 'required|boolean',

        ]);

        $user = User::where('shop_id', $request->shop_id)->first();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        $shopifySettings = ShopifySetting::where('shop_id', $request->shop_id)->first();

        if (! $shopifySettings) {
            return response()->json([
                'status' => false,
                'message' => 'Shopify settings not found',
            ], 404);
        }

        if ((bool) $request->auto !== (bool) $shopifySettings->auto_sync) {
            return response()->json([
                'status' => false,
                'message' => 'Auto sync setting mismatch. Please refresh your Shopify settings before syncing.',
                'expected_auto' => (bool) $shopifySettings->auto_sync,
                'received_auto' => (bool) $request->auto,
            ], 409);
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

                // Save real product titles

                $items = $orderItems->pluck('name')->implode(', ');

                // Total quantity

                $quantity = $orderItems->sum('count');

                // Extract size from Shopify title

                $sizes = [];

                foreach ($orderItems as $item) {

                    $name = $item['name'] ?? '';

                    // Example: "Filler Pack 2 - L / DD / Rubber"

                    if (str_contains($name, ' - ')) {

                        $parts = explode(' - ', $name, 2);

                        if (! empty($parts[1])) {

                            $sizes[] = trim(explode('/', $parts[1])[0]);

                        }

                    }

                }

                $size = ! empty($sizes) ? implode(', ', array_unique($sizes)) : null;

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
| CITY + AREA
|------------------------------------------
*/
                $provinceName = $shopifyOrder['shippingDetails']['province'] ?? null;

                $shipping = $shopifyOrder['shippingDetails'] ?? [];

                $fullAddress = collect([
                    $shipping['address1'] ?? null,
                    $shipping['address2'] ?? null,
                    $shipping['city'] ?? null,
                    $shipping['province'] ?? null,
                    $shipping['country'] ?? null,
                ])
                    ->filter()
                    ->implode(' ');

                $city = City::where('name', $provinceName)->first();

                $finalArea = null;

                if ($city) {

                    $branchId = $user->branch_id;

                    /*
                    |------------------------------------------
                    | SELECT AREA TABLE BY SHIPPER BRANCH
                    |------------------------------------------
                    */
                    if ($branchId == 2) {
                        $areas = AreaTier1::where('city_id', $city->id)->get();
                    } elseif ($branchId == 4) {
                        $areas = AreaTier2::where('city_id', $city->id)->get();
                    } else {
                        $areas = Area::where('city_id', $city->id)->get();
                    }

                    /*
                    |------------------------------------------
                    | TOKENIZE ADDRESS
                    |------------------------------------------
                    */
                    $address = strtolower($fullAddress);

                    $addressTokens = array_filter(
                        preg_split(
                            '/\s+/',
                            preg_replace('/[^a-z0-9\s]/', ' ', $address)
                        )
                    );

                    $bestScore = -1;

                    foreach ($areas as $area) {

                        $areaName = strtolower($area->name);

                        $areaTokens = array_filter(
                            preg_split(
                                '/\s+/',
                                preg_replace('/[^a-z0-9\s]/', ' ', $areaName)
                            )
                        );

                        $score = 0;

                        /*
                        |------------------------------------------
                        | EXACT AREA NAME MATCH
                        |------------------------------------------
                        */
                        if (str_contains($address, $areaName)) {
                            $score += 100;
                        }

                        /*
                        |------------------------------------------
                        | TOKEN MATCHES
                        |------------------------------------------
                        */
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

                        /*
                        |------------------------------------------
                        | CITY BOOST
                        |------------------------------------------
                        */
                        if (str_contains($address, strtolower($city->name))) {
                            $score += 30;
                        }

                        /*
                        |------------------------------------------
                        | BEST MATCH
                        |------------------------------------------
                        */
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

                $shipping = $shopifyOrder['shippingDetails'] ?? [];

                $fullAddress = collect([
                    $shipping['address1'] ?? null,
                    $shipping['address2'] ?? null,
                    $shipping['city'] ?? null,
                    $shipping['province'] ?? null,
                    $shipping['country'] ?? null,
                    $shipping['provinceCode'] ?? null,
                ])
                    ->filter()
                    ->implode(', ');

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

                    'receiver_address' => $fullAddress,

                    'cod_amount' => $shopifyOrder['outstandingAmount'] ?? 0,

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
