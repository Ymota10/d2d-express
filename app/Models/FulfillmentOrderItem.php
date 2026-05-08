<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FulfillmentOrderItem extends Model
{
    protected $fillable = [
        'fulfillment_order_id',
        'warehouse_item_id',
        'quantity',
    ];

    public function item()
    {
        return $this->belongsTo(WarehouseItem::class, 'warehouse_item_id');
    }
}
