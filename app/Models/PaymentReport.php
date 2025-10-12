<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReport extends Model
{
    protected $fillable = [
        'user_id',
        'order_ids',
        'total_cod',
        'total_delivery_cost',
        'extra_fees',
        'shipper_id',
        'final_amount',
    ];

    protected $casts = [
        'order_ids' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function orders()
    // {
    //     return $this->hasMany(Order::class, 'id', 'order_ids');
    // }

    // // public function orders()
    // // {
    // //     return Order::whereIn('id', $this->order_ids)->get();
    // // }

    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipper_id');
    }

    public function getOrdersAttribute()
    {
        return Order::whereIn('id', json_decode($this->order_ids, true) ?? [])->get();
    }
}
