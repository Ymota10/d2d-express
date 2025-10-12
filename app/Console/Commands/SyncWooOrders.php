<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\WooCommerceService;
use Illuminate\Console\Command;

class SyncWooOrders extends Command
{
    protected $signature = 'sync:woo-orders';

    protected $description = 'Sync WooCommerce orders into the system';

    protected $woo;

    public function __construct(WooCommerceService $woo)
    {
        parent::__construct();
        $this->woo = $woo;
    }

    public function handle()
    {
        $this->info('Fetching WooCommerce orders...');
        $orders = $this->woo->getOrders(['per_page' => 50]);

        foreach ($orders as $wooOrder) {
            Order::updateOrCreate(
                ['woo_id' => $wooOrder['id']],
                [
                    'receiver_name' => $wooOrder['billing']['first_name'].' '.$wooOrder['billing']['last_name'],
                    'receiver_mobile_1' => $wooOrder['billing']['phone'],
                    'receiver_address' => $wooOrder['billing']['address_1'],
                    'item_name' => $wooOrder['line_items'][0]['name'] ?? 'Unknown Item',
                    'cod_amount' => $wooOrder['total'],
                    'quantity' => $wooOrder['line_items'][0]['quantity'] ?? 1,
                    'status' => $this->mapWooStatus($wooOrder['status']),
                ]
            );
        }

        $this->info('WooCommerce orders synced successfully!');
    }

    private function mapWooStatus($status)
    {
        return match ($status) {
            'pending' => 'pickup_request',
            'processing' => 'warehouse_received',
            'completed' => 'success_delivery',
            'cancelled' => 'returned_to_shipper',
            default => 'pickup_request',
        };
    }
}
