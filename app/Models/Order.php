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
        'return_cost',

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
        'time_scheduled_at',
        'is_printed',

        // Insurance
        'insurance_package_id',
        'insurance_fee',
        'insured_amount',

        // Externals
        'source',
        'external_order_id',
        'external_payload',

    ];

    /*
    |--------------------------------------------------------------------------
    | User / Role Helpers
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Location Relationships
    |--------------------------------------------------------------------------
    */

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Area Relationships
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | area_id can point to:
    |
    | Branch 2 -> areas_tier_1
    | Branch 4 -> areas_tier_2
    | Other    -> areas
    |
    | These must remain separate relationships because Eloquent cannot
    | safely change the related model class dynamically per Order record.
    |
    */

    // Normal / default areas
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    // Branch 2
    public function areaTier1()
    {
        return $this->belongsTo(AreaTier1::class, 'area_id');
    }

    // Branch 4
    public function areaTier2()
    {
        return $this->belongsTo(AreaTier2::class, 'area_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Resolved Area
    |--------------------------------------------------------------------------
    |
    | Use:
    |
    | $order->resolved_area
    |
    | or:
    |
    | $order->resolved_area?->name
    |
    | This automatically selects the correct area table according to
    | the shipper's branch.
    |
    */

    public function getResolvedAreaAttribute()
    {
        $branchId = $this->shipper?->branch_id
            ?? $this->user?->branch_id;

        if ($branchId == 2) {
            return $this->areaTier1;
        }

        if ($branchId == 4) {
            return $this->areaTier2;
        }

        return $this->area;
    }

    /*
    |--------------------------------------------------------------------------
    | Shipper / User Relationships
    |--------------------------------------------------------------------------
    */

    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipper_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branches::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Other Relationships
    |--------------------------------------------------------------------------
    */

    public function attempts()
    {
        return $this->hasMany(OrderAttempt::class);
    }

    public function insurancePackage()
    {
        return $this->belongsTo(InsurancePackage::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($order) {

            // Mirror users_id to shipper_id
            if (empty($order->shipper_id) && $order->users_id) {
                $order->shipper_id = $order->users_id;
            }

            $shipper = User::find($order->users_id);

            if (! $shipper?->waybill_prefix) {
                throw new \RuntimeException(
                    'Waybill prefix is not configured for this shipper.'
                );
            }

            $prefix = strtoupper(trim($shipper->waybill_prefix));

            do {
                $number = str_pad(
                    random_int(1, 999999),
                    6,
                    '0',
                    STR_PAD_LEFT
                );

                $waybill = $prefix.'-'.$number;

            } while (
                static::where('waybill_number', $waybill)->exists()
            );

            $order->waybill_number = $waybill;
        });
    }
}
