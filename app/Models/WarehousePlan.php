<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehousePlan extends Model
{
    protected $fillable = [
        'name',
        'billing_type',
        'monthly_price',
        'order_fee',
    ];

    public function subscriptions()
    {
        return $this->hasMany(WarehouseSubscription::class);
    }
}
