<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipper extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'management',
        'name',
        'phone',
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function city()
    {
        return $this->belongsTo(\App\Models\City::class, 'city_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branches::class, 'branch_id');
    }
}
