<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseItem extends Model
{
    protected $fillable = [
        'user_id',
        'sku',
        'name',
        'description',
    ];

    public function stock()
    {
        return $this->hasOne(WarehouseStock::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
