<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class BostaService
{
    protected $baseUrl;

    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.bosta.base_url'), '/').'/deliveries?apiVersion=1';
        $this->apiKey = config('services.bosta.api_key');
    }

    public function createShipment(Order $order)
    {
        $payload = [
            'type' => 10, // 10 = delivery
            'specs' => [
                'size' => 'MEDIUM',
                'packageType' => 'Parcel',
            ],
            'cod' => (float) ($order->cod_amount ?? 0),
            'businessReference' => (string) ($order->order_id ?? $order->id),
            'uniqueBusinessReference' => 'D2D-'.($order->id ?? uniqid()),
            'notes' => $order->notes ?? '',

            'receiver' => [
                'firstName' => $order->receiver_name ?? 'Receiver',
                'lastName' => '',
                'phone' => $order->receiver_mobile_1 ?? '01000000000',
                'secondPhone' => $order->receiver_mobile_2 ?? null,
                'email' => '',
            ],

            'dropOffAddress' => [
                'city' => $order->city->name ?? 'Cairo',
                'districtName' => $order->area->name ?? '',
                'firstLine' => $order->receiver_address ?? 'No address provided',
                'isWorkAddress' => false,
            ],

            'allowToOpenPackage' => (bool) ($order->open_package ?? false),
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, $payload);

        if ($response->successful()) {
            $data = $response->json();

            $order->update([
                'waybill_number' => $data['trackingNumber'] ?? $data['_id'] ?? null,
            ]);

            \Log::info('✅ Bosta Order Synced', [
                'order_id' => $order->id,
                'response' => $data,
            ]);

            return $data;
        }

        \Log::error('❌ Bosta API Error', [
            'status' => $response->status(),
            'body' => $response->body(),
            'payload' => $payload,
        ]);

        return null;
    }
}
