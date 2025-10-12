<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flyer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'size', 'price', 'pack_size',
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
        return $this->belongsTo(\App\Models\City::class, 'city_id');
    }

    public function area()
    {
        return $this->belongsTo(\App\Models\Area::class, 'area_id');
    }

    public function shipper()
    {
        return $this->belongsTo(\App\Models\Shipper::class);
    }

    // public function branch()
    // {
    //     return $this->belongsTo(Branches::class);
    // }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'users_id');
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($order) {
    //         // Generate a unique waybill starting with DD and a random 6-digit number
    //         $order->waybill_number = 'DD'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    //     });
    // }
}
