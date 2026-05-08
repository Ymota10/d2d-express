<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'warehouse_plan_id',
        'start_date',
        'next_billing_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(WarehousePlan::class, 'warehouse_plan_id');
    }
}
