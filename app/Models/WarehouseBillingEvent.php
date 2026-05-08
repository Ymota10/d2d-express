<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseBillingEvent extends Model
{
    protected $fillable = [
        'user_id',
        'fulfillment_order_id',
        'amount',
        'description',
        'billing_month',
    ];
}
