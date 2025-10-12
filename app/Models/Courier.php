<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'management',
        'name',
        'phone',
        'email',
        'national_id',
        'city_id',
        'address',
        'password',
        'branch_id',
        'profile_photo',
        'gender',
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

    public function Area()
    {
        return $this->hasMany(Area::class, 'city_id');
    }

    public function Branch()
    {
        return $this->hasMany(Branches::class, 'id');
    }

    public function city()
    {
        return $this->belongsTo(\App\Models\City::class, 'city_id');
    }
}
