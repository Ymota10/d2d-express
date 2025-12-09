<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [

        'waybill_number',
        'is_collected',

        'users_id',
        'shipper_id',
        'area_id',
        'city_id',

        // Sender Info
        'sender_name',
        'sender_phone',
        'sender_address',
        'sender_area',

        // Receiver Info
        'receiver_mobile_1',
        'receiver_mobile_2',
        'receiver_name',
        'client_id',
        'receiver_address',
        'receiver_area',
        'delivery_cost',

        // Shipment Data
        'item_name',
        'description',
        'notes',
        'order_id',
        'flyer_no',
        'cod_amount',
        'service_type',
        'open_package',
        'open_package_fee',
        'weight',
        'size',
        'quantity',
        'status',
        'undelivered_reason',

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

    /**
     * Dynamic area relationship
     * Branch 2 → AreaTier1
     * Others → Area
     */
    public function area()
    {
        $branchId = $this->users?->branch_id ?? $this->shipper?->branch_id ?? null;

        if ($branchId == 2) {
            return $this->belongsTo(\App\Models\AreaTier1::class, 'area_id');
        }

        if ($branchId == 4) {
            return $this->belongsTo(\App\Models\AreaTier2::class, 'area_id');
        }

        return $this->belongsTo(\App\Models\Area::class, 'area_id');
    }

    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipper_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branches::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'users_id');
    }

    public function attempts()
    {
        return $this->hasMany(\App\Models\OrderAttempt::class);
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            // Mirror users_id to shipper_id
            if (empty($order->shipper_id) && $order->users_id) {
                $order->shipper_id = $order->users_id;
            }

            // Generate unique waybill
            $order->waybill_number = 'DD'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        });
    }
}
