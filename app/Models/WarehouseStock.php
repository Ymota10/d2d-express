<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    protected $fillable = [
        'warehouse_item_id',
        'sku',
        'product_name',
        'quantity',
        'user_id',
    ];

    // Relation to WarehouseItem
    public function item()
    {
        return $this->belongsTo(WarehouseItem::class, 'warehouse_item_id');
    }

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
