<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseStockMovement extends Model
{
    protected $fillable = [
        'warehouse_item_id',
        'fulfillment_order_id',
        'type',
        'quantity',
    ];
}
