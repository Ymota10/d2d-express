<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PollFlextockStatuses extends Command
{
    protected $signature = 'flextock:poll-statuses';

    protected $description = 'Synchronize local order statuses with Flextock';

    public function handle(): int
    {
        $baseUrl = rtrim(
            config('services.flextock.base_url', 'https://api.flextock.com'),
            '/'
        );

        try {
            // Authenticate once for this polling run.
            $authResponse = Http::timeout(30)
                ->acceptJson()
                ->post($baseUrl.'/base/auth/', [
                    'username' => config('services.flextock.username'),
                    'password' => config('services.flextock.password'),
                    'key' => config('services.flextock.api_key'),
                ]);

            if (! $authResponse->successful()) {
                Log::error('Flextock polling authentication failed', [
                    'http_status' => $authResponse->status(),
                    'response' => $authResponse->json(),
                ]);

                $this->error('Flextock authentication failed.');

                return self::FAILURE;
            }

            $token = $authResponse->json('access');

            if (! $token) {
                Log::error('Flextock polling: access token missing.');
                $this->error('Flextock returned no access token.');

                return self::FAILURE;
            }

            /*
             * IMPORTANT:
             * This query assumes orders created through SyncOrdersController
             * are Flextock orders, use source=shopify, and have WB- waybills.
             *
             * If other courier integrations use the same criteria, add a
             * dedicated provider marker before enabling automatic polling.
             */
            $orders = Order::query()
                ->whereNotNull('waybill_number')
                ->where('waybill_number', '!=', '')
                ->get([
                    'id',
                    'waybill_number',
                    'users_id',
                    'order_id',
                    'status',
                ]);

            if ($orders->isEmpty()) {
                $this->info('No eligible Flextock orders found.');

                return self::SUCCESS;
            }

            $ordersByWaybill = $orders->keyBy('waybill_number');
            $updated = 0;
            $unchanged = 0;
            $unmatched = 0;

            foreach ($ordersByWaybill->keys()->chunk(100) as $waybills) {
                $response = Http::withToken($token)
                    ->acceptJson()
                    ->timeout(60)
                    ->post($baseUrl.'/external-integration/order-status/', [
                        'order_code_array' => $waybills->values()->all(),
                    ]);

                Log::info('Flextock polling API diagnostic', [
                    'http_status' => $response->status(),
                    'successful' => $response->successful(),
                    'response_body' => $response->json() ?? $response->body(),
                ]);

                if (! $response->successful()) {
                    Log::error('Flextock status batch request failed', [
                        'http_status' => $response->status(),
                        'waybills_count' => $waybills->count(),
                        'response' => $response->json(),
                    ]);

                    $this->warn('A Flextock status batch failed; continuing.');

                    continue;
                }

                $rows = $this->extractStatusRows($response->json());

                if ($rows === null) {
                    Log::warning('Unrecognized Flextock batch response format', [
                        'response' => $response->json(),
                        'requested_waybills' => $waybills->values()->all(),
                    ]);

                    $this->warn(
                        'Could not interpret a batch response. No orders in that batch were updated.'
                    );

                    continue;
                }

                foreach ($rows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    $waybill = $row['order_code']
                        ?? $row['orderCode']
                        ?? $row['code']
                        ?? null;

                    /*
                     * Some APIs return results keyed by order_code.
                     * extractStatusRows() normalizes those rows where possible.
                     */
                    if (! $waybill) {
                        continue;
                    }

                    $order = $ordersByWaybill->get((string) $waybill);

                    if (! $order) {
                        $unmatched++;

                        continue;
                    }

                    // Save Flextock courier tracking details independently of order status.
                    $courierName = $row['courier_name'] ?? null;
                    $trackingNumber = $row['tracking_number'] ?? null;
                    $trackingUrl = $row['tracking_url'] ?? null;

                    // Preserve existing values when Flextock omits or returns null metadata.
                    $courierName = is_string($courierName) && trim($courierName) !== ''
                        ? trim($courierName)
                        : $order->flextock_courier_name;

                    $trackingNumber = is_string($trackingNumber) && trim($trackingNumber) !== ''
                        ? trim($trackingNumber)
                        : $order->courier_tracking_number;

                    $trackingUrl = is_string($trackingUrl) && trim($trackingUrl) !== ''
                        ? trim($trackingUrl)
                        : $order->courier_tracking_url;

                    $metadataChanged = false;

                    if ($order->flextock_courier_name !== $courierName) {
                        $order->flextock_courier_name = $courierName;
                        $metadataChanged = true;
                    }

                    if ($order->courier_tracking_number !== $trackingNumber) {
                        $order->courier_tracking_number = $trackingNumber;
                        $metadataChanged = true;
                    }

                    if ($order->courier_tracking_url !== $trackingUrl) {
                        $order->courier_tracking_url = $trackingUrl;
                        $metadataChanged = true;
                    }

                    if ($metadataChanged) {
                        $order->save();

                        Log::info('Flextock courier tracking details synchronized', [
                            'order_id' => $order->id,
                            'waybill_number' => $order->waybill_number,
                            'courier_name' => $courierName,
                            'tracking_number' => $trackingNumber,
                            'tracking_url' => $trackingUrl,
                        ]);
                    }

                    $flextockStatus = $row['order_status']
                        ?? $row['status']
                        ?? null;

                    $subStatus = $row['sub_status']
                        ?? $row['substatus']
                        ?? null;

                    if (! is_string($flextockStatus) || trim($flextockStatus) === '') {
                        $unchanged++;

                        continue;
                    }

                    $mappedStatus = $this->mapStatus(
                        $flextockStatus,
                        is_string($subStatus) ? $subStatus : null
                    );

                    // Unknown or ambiguous statuses must not overwrite local status.
                    if ($mappedStatus === null) {
                        $unchanged++;

                        continue;
                    }

                    // Avoid duplicate Shopify notifications on every polling run.
                    if ($order->status === $mappedStatus) {
                        $unchanged++;

                        continue;
                    }

                    $oldStatus = $order->status;
                    $order->status = $mappedStatus;
                    $order->save();

                    $this->syncShopify($order, $mappedStatus);

                    Log::info('Flextock order status synchronized', [
                        'order_id' => $order->id,
                        'waybill_number' => $order->waybill_number,
                        'flextock_status' => $flextockStatus,
                        'flextock_sub_status' => $subStatus,
                        'old_status' => $oldStatus,
                        'new_status' => $mappedStatus,
                    ]);

                    $updated++;
                }
            }

            $this->info(
                "Flextock polling finished. Updated: {$updated}; ".
                "unchanged/unmapped: {$unchanged}; unmatched: {$unmatched}."
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Flextock status polling exception', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            $this->error('Flextock polling failed. Check the application log.');

            return self::FAILURE;
        }
    }

    /**
     * Normalize common batch response structures.
     *
     * The exact batch response format should be verified against a real
     * response from Flextock before relying on automatic polling.
     */
    private function extractStatusRows(mixed $json): ?array
    {
        if (! is_array($json)) {
            return null;
        }

        // Actual Flextock batch response.
        if (array_key_exists('successful_orders', $json)) {
            return is_array($json['successful_orders'])
                ? $json['successful_orders']
                : null;
        }

        foreach (['results', 'data', 'orders', 'order_statuses'] as $key) {
            if (isset($json[$key]) && is_array($json[$key])) {
                $json = $json[$key];
                break;
            }
        }

        if (array_is_list($json)) {
            return $json;
        }

        if (isset($json['order_code']) || isset($json['order_status'])) {
            return [$json];
        }

        $rows = [];

        foreach ($json as $code => $value) {
            if (is_array($value)) {
                $value['order_code'] ??= (string) $code;
                $rows[] = $value;
            }
        }

        return $rows !== [] ? $rows : null;
    }

    private function mapStatus(string $status, ?string $subStatus): ?string
    {
        $status = strtolower(trim($status));
        $subStatus = strtolower(trim((string) $subStatus));

        return match ($status) {
            // Ambiguous without a substatus: leave the current status unchanged.
            'on hold' => match ($subStatus) {
                'no answer', 'waiting for address' => 'failed_attempt',
                default => null,
            },

            // Pending and ready do not determine a new LYNK status.
            'pending' => null,
            'ready' => null,

            'processing' => match ($subStatus) {
                '' => 'warehouse_received',
                'rescheduled' => 'time_scheduled',
                default => null,
            },

            'fulfilled', 'received at fc' => 'warehouse_received',

            'pick up', 'picked by driver' => null,

            'in transit' => 'out_for_delivery',

            'returning' => $subStatus === 'rto'
                ? 'undelivered'
                : null,

            'delivered' => $subStatus === ''
                ? 'success_delivery'
                : null,

            'returned to origin' => 'undelivered',

            'canceled', 'cancelled' => 'undelivered',

            'lost' => 'lost',

            'partially delivered' => 'partial_return',

            default => null,
        };
    }

    private function syncShopify(Order $order, string $status): void
    {
        $user = User::find($order->users_id);

        if (! $user || ! $user->shop_id) {
            return;
        }

        $url = rtrim((string) config('services.shopify_internal.url'), '/');
        $key = config('services.shopify_internal.key');

        if ($url === '' || ! $key) {
            Log::warning('Shopify status sync skipped: internal API configuration missing.', [
                'order_id' => $order->id,
            ]);

            return;
        }

        $headers = ['x-internal-key' => $key];

        // Create Shopify fulfillment when the parcel reaches the warehouse.
        if ($status === 'warehouse_received') {
            $fulfillmentResponse = Http::withHeaders($headers)
                ->timeout(30)
                ->post($url.'/internal/shopify/fulfill-orders', [
                    'shop' => $user->shop_id,
                    'orders' => [[
                        'shopifyOrderId' => $order->order_id,
                        'trackingNumber' => $order->waybill_number,
                        'trackingCompany' => 'LYNK',
                        'trackingUrl' => 'https://dashboard.lynk-eg.com/admin/track?waybill='.
                            urlencode($order->waybill_number),
                        'notifyCustomer' => true,
                    ]],
                ]);

            if (! $fulfillmentResponse->successful()) {
                Log::error('Shopify fulfillment request failed after Flextock update', [
                    'order_id' => $order->id,
                    'http_status' => $fulfillmentResponse->status(),
                    'response' => $fulfillmentResponse->json(),
                ]);
            }
        }

        $shopifyStatus = match ($status) {
            'out_for_delivery' => [
                'status' => 'OUT_FOR_DELIVERY',
                'message' => 'Order is out for delivery by D2DExpress',
            ],
            'success_delivery' => [
                'status' => 'DELIVERED',
                'message' => 'Order delivered by D2DExpress',
            ],
            'failed_attempt' => [
                'status' => 'FAILURE',
                'message' => 'Delivery attempt failed',
            ],
            'time_scheduled' => [
                'status' => 'IN_TRANSIT',
                'message' => 'Order is in transit',
            ],
            'returned_to_warehouse' => [
                'status' => 'IN_TRANSIT',
                'message' => 'Order returned to warehouse and is still in transit',
            ],
            default => null,
        };

        if ($shopifyStatus === null) {
            return;
        }

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($url.'/internal/shopify/update-fulfillment', [
                'shop' => $user->shop_id,
                'orders' => [[
                    'shopifyOrderId' => $order->order_id,
                    'status' => $shopifyStatus['status'],
                    'message' => $shopifyStatus['message'],
                ]],
            ]);

        if (! $response->successful()) {
            Log::error('Shopify status update failed after Flextock update', [
                'order_id' => $order->id,
                'http_status' => $response->status(),
                'response' => $response->json(),
            ]);
        }
    }
}
