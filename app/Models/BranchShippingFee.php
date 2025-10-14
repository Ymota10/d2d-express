<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchShippingFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'city_id',
        'delivery_cost',
        'return_cost',
        'replacement_partial_delivery_cost',
        'overweight_cost',
        'refund_cost',
        'exchange_cost',
    ];

    /**
     * Each fee belongs to a branch.
     */
    public function branch()
    {
        return $this->belongsTo(Branches::class, 'branch_id');
    }

    /**
     * Each fee belongs to a city.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
