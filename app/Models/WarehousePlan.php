<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehousePlan extends Model
{
    protected $fillable = ['name', 'monthly_price', 'included_orders', 'overage_fee'];
}
