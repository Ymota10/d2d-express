<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FulfillmentOrder extends Model
{
    protected $fillable = [
        'user_id',
        'warehouse_subscription_id',
        'shipping_order_id',
        'reference',
        'status',
    ];

    public function orderItems()
    {
        return $this->hasMany(FulfillmentOrderItem::class);
    }

    public function subscription()
    {
        return $this->belongsTo(WarehouseSubscription::class, 'warehouse_subscription_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::deleted(function (FulfillmentOrder $order) {
            // Restore stock only if the order was already packed or shipped
            if (in_array($order->status, ['packed', 'shipped'])) {
                $stockService = app(WarehouseStockService::class);

                foreach ($order->items as $item) {
                    $stockService->in(
                        $item->warehouse_item_id,
                        $item->quantity,
                        $order->id
                    );
                }
            }
        });
    }
}
