<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->management !== 'track_express';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $order = $this->record;

        /*
        |--------------------------------------------------------------------------
        | FLEXTOCK AUTHENTICATION
        |--------------------------------------------------------------------------
        */
        try {
            $baseUrl = rtrim(
                config('services.flextock.base_url'),
                '/'
            );

            $authResponse = Http::timeout(30)->post(
                $baseUrl.'/base/auth/',
                [
                    'username' => config('services.flextock.username'),
                    'password' => config('services.flextock.password'),
                    'key' => config('services.flextock.api_key'),
                ]
            );

            if (! $authResponse->successful()) {
                Log::error('Flextock authentication failed for manual order', [
                    'order_id' => $order->id,
                    'status' => $authResponse->status(),
                    'response' => $authResponse->json(),
                ]);

                return;
            }

            $accessToken = $authResponse->json('access');

            if (! $accessToken) {
                Log::error('Flextock returned no access token for manual order', [
                    'order_id' => $order->id,
                    'response' => $authResponse->json(),
                ]);

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | CUSTOMER NAME
            |--------------------------------------------------------------------------
            */
            $nameParts = preg_split(
                '/\s+/',
                trim((string) $order->receiver_name),
                2
            );

            $firstName = $nameParts[0] ?? 'Customer';
            $lastName = $nameParts[1] ?? '';

            /*
            |--------------------------------------------------------------------------
            | PAYMENT DATA
            |--------------------------------------------------------------------------
            |
            | Manual orders are treated as COD when cod_amount > 0.
            | Otherwise, they are sent as prepaid.
            |
            */
            $codAmount = (float) ($order->cod_amount ?? 0);
            $isCod = $codAmount > 0;

            $paymentData = [
                [
                    'value' => $isCod
                        ? $codAmount
                        : 0,

                    'payment_type' => $isCod
                        ? 'cash_on_delivery'
                        : 'prepaid',

                    'payment_method' => $isCod
                        ? null
                        : 'prepaid',

                    'payment_reference' => (string) $order->id,

                    'payment_timestamp' => now()->toIso8601String(),
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | LINE ITEM
            |--------------------------------------------------------------------------
            |
            | Uses the same fixed Flextock SKU as Shopify integration.
            |
            */
            $lineItemPrice = max(
                0,
                $codAmount - (float) ($order->delivery_cost ?? 0)
            );

            $lineItems = [
                [
                    'sku_code' => '1111',
                    'quantity' => 1,
                    'sku_price' => $lineItemPrice,
                    'sku_promotional_price' => null,
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | FLEXTOCK PAYLOAD
            |--------------------------------------------------------------------------
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
                    'country' => 'Egypt',
                    'city' => $order->city?->name,
                    'area' => $order->area?->name,

                    'address_line1' => $order->receiver_address ?? '',
                    'address_line2' => null,

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

                'requires_self_delivery' => false,
            ];

            /*
            |--------------------------------------------------------------------------
            | CREATE ORDER IN FLEXTOCK
            |--------------------------------------------------------------------------
            */
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(30)
                ->post(
                    $baseUrl.'/external-integration/create-order/',
                    $payload
                );

            if ($response->successful()) {
                Log::info('Flextock manual order created successfully', [
                    'd2d_order_id' => $order->id,
                    'waybill_number' => $order->waybill_number,
                    'response' => $response->json(),
                ]);
            } else {
                Log::error('Flextock manual order creation failed', [
                    'd2d_order_id' => $order->id,
                    'waybill_number' => $order->waybill_number,
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'payload' => $payload,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Flextock manual order integration exception', [
                'd2d_order_id' => $order->id,
                'waybill_number' => $order->waybill_number,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
