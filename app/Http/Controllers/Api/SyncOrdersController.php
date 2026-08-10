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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncOrdersController extends Controller
{
    public function sync(Request $request)
    {

        Log::info('Full Sync Payload', $request->all());
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

                    'cod_amount' => ($shopifyOrder['fullyPaid'] ?? false)
                     ? 0
                    : (float) ($shopifyOrder['totalPrice'] ?? 0),

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

                /*
                |------------------------------------------
                | CREATE ORDER IN FLEXTOCK
                |------------------------------------------
                */
                $this->createFlextockOrder($order, $shopifyOrder);

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

    private function createFlextockOrder(Order $order, array $shopifyOrder): void
    {
        try {

            /*
            |------------------------------------------
            | AUTHENTICATE WITH FLEXTOCK
            |------------------------------------------
            */
            $authResponse = Http::post(
                config('services.flextock.base_url').'/base/auth/',
                [
                    'username' => config('services.flextock.username'),
                    'password' => config('services.flextock.password'),
                    'key' => config('services.flextock.api_key'),
                ]
            );

            if (! $authResponse->successful()) {

                Log::error('Flextock authentication failed', [
                    'order_id' => $order->id,
                    'status' => $authResponse->status(),
                    'response' => $authResponse->json(),
                ]);

                return;
            }

            $accessToken = $authResponse->json('access');

            if (! $accessToken) {

                Log::error('Flextock authentication returned no access token', [
                    'order_id' => $order->id,
                    'response' => $authResponse->json(),
                ]);

                return;
            }

            /*
            |------------------------------------------
            | CUSTOMER DATA
            |------------------------------------------
            */
            $customer = $shopifyOrder['customerDetails'] ?? [];
            $shipping = $shopifyOrder['shippingDetails'] ?? [];

            /*
            |------------------------------------------
            | SPLIT CUSTOMER NAME
            |------------------------------------------
            */
            $firstName = $customer['firstName']
                ?? $shipping['name']
                ?? 'Customer';

            $lastName = $customer['lastName'] ?? '';

            /*
            |------------------------------------------
            | FLEXTOCK ORDER PAYMENTS
            |------------------------------------------
            |
            | Fully paid Shopify order:
            | payment_type = prepaid
            | value = total price
            |
            | COD Shopify order:
            | payment_type = cash_on_delivery
            | value = COD amount
            |
            */
            $isFullyPaid = (bool) ($shopifyOrder['fullyPaid'] ?? false);

            $paymentData = [
                [
                    'value' => $isFullyPaid
                        ? (float) ($shopifyOrder['totalPrice'] ?? 0)
                        : (float) ($order->cod_amount ?? 0),

                    'payment_type' => $isFullyPaid
                        ? 'prepaid'
                        : 'cash_on_delivery',

                    'payment_method' => $isFullyPaid
                        ? ($shopifyOrder['paymentGatewayNames'][0] ?? 'online')
                        : null,

                    'payment_reference' => $isFullyPaid
                        ? ($shopifyOrder['id'] ?? null)
                        : null,

                    'payment_timestamp' => $isFullyPaid
                        ? ($shopifyOrder['createdAt'] ?? now()->toIso8601String())
                        : now()->toIso8601String(),
                ],
            ];

            /*
            |------------------------------------------
            | LINE ITEM
            |------------------------------------------
            |
            | One fixed Flextock product is used for
            | every D2D order.
            |
            | SKU      = 1111
            | Quantity = 1
            | Price    = 999
            */
            $lineItems = [
                [
                    'sku_code' => '1111',
                    'quantity' => 1,
                    'sku_price' => 999,
                    'sku_promotional_price' => null,
                ],
            ];

            /*
            |------------------------------------------
            | FLEXTOCK PAYLOAD
            |------------------------------------------
            */
            $payload = [
                'order_code' => $order->waybill_number,

                'order_date' => $order->created_at
                    ? $order->created_at->format('Y-m-d')
                    : now()->format('Y-m-d'),

                'shipping_fees' => (float) ($order->delivery_cost ?? 0),

                'is_free_shipping' => false,

                'is_gift_order' => false,

                'order_currency' => 'EGP',

                'integration_source' => 'D2D Express',

                'vendor_name' => 'D2D Express',

                'customer_address' => [
                    'country' => $shipping['country'] ?? 'Egypt',
                    'city' => $shipping['province'] ?? $shipping['city'] ?? null,
                    'area' => $shipping['city'] ?? null,

                    'address_line1' => $shipping['address1']
                        ?? $order->receiver_address
                        ?? '',

                    'address_line2' => $shipping['address2'] ?? null,

                    'building_no' => null,
                    'floor_no' => null,
                    'apartment_no' => null,

                    'is_work_address' => false,

                    'first_name' => $firstName,
                    'last_name' => $lastName,

                    'phone_number' => $order->receiver_mobile_1,
                    'secondary_phone_number' => $order->receiver_mobile_2,

                    'note' => $order->notes,
                ],

                'line_items' => $lineItems,

                'extra_fees' => [],

                'payment_data' => $paymentData,

                'discounts_data' => [],

                /*
                |------------------------------------------
                | FLEXTOCK DELIVERY
                |------------------------------------------
                |
                | false = Flextock handles delivery.
                |
                | This is currently the default.
                */
                'requires_self_delivery' => false,
            ];

            /*
            |------------------------------------------
            | SEND ORDER TO FLEXTOCK
            |------------------------------------------
            */
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post(
                    config('services.flextock.base_url').'/external-integration/create-order/',
                    $payload
                );

            /*
            |------------------------------------------
            | LOG RESULT
            |------------------------------------------
            */
            if ($response->successful()) {

                Log::info('Flextock order created successfully', [
                    'd2d_order_id' => $order->id,
                    'waybill_number' => $order->waybill_number,
                    'response' => $response->json(),
                ]);

            } else {

                Log::error('Flextock order creation failed', [
                    'd2d_order_id' => $order->id,
                    'waybill_number' => $order->waybill_number,
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'payload' => $payload,
                ]);
            }

        } catch (\Throwable $e) {

            Log::error('Flextock order integration exception', [
                'd2d_order_id' => $order->id,
                'waybill_number' => $order->waybill_number,
                'message' => $e->getMessage(),
            ]);
        }
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
