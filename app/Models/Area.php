<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'city_id',
        'delivery_cost',
        'return_cost',
        'overweight_cost',
        'refund_cost',
        'exchange_cost',
        'replacement_partial_delivery_cost',
    ];

    public function isAdmin(): bool
    {
        return $this->management === 'admin';
    }

    public function isShipper(): bool
    {
        return $this->management === 'shipper';
    }

    public function isCourier(): bool
    {
        return $this->management === 'courier';
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
