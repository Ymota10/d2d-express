<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsurancePackage extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'percentage',
        'minimum_fee',
        'max_compensation',
        'covers_loss',
        'covers_damage',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'covers_loss' => 'boolean',
        'covers_damage' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
