<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branches extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'branch_type',
        'shipping_fees',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Branch has many shipping fees per city.
     */
    public function shippingFees()
    {
        return $this->hasMany(BranchShippingFee::class, 'branch_id');
    }
}
