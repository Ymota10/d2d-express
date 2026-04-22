<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifySetting extends Model
{
    protected $fillable = [
        'shop_id',
        'auto_sync',
        'fulfillment_option',
    ];
}
