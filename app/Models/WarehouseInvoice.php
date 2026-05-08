<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseInvoice extends Model
{
    protected $fillable = [
        'user_id',
        'billing_month',
        'rent_amount',
        'usage_amount',
        'total_amount',
        'status',
    ];
}
