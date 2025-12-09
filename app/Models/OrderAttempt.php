<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAttempt extends Model
{
    protected $fillable = [
        'order_id',
        'attempt_number',
        'status',
        'note',
        'attempted_at',
    ];

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }
}
