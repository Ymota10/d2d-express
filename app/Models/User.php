<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'management',        // admin, courier, shipper
        'name',
        'email',
        'phone',
        'phone_secondary',
        'national_id',
        'city_id',
        'branch_id',
        'address',
        'profile_photo',
        'gender',
        'email_verified_at',
        'password',
        'image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** ────────────────────────────────
     *  ROLE CHECKERS
     *  ──────────────────────────────── */
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

    /** ────────────────────────────────
     *  RELATIONSHIPS
     *  ──────────────────────────────── */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branches::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function shipper()
    {
        return $this->hasOne(\App\Models\Shipper::class);
    }

    /** ────────────────────────────────
     *  AUTO-CREATE SHIPPER WHEN USER IS CREATED
     *  ──────────────────────────────── */
    protected static function booted(): void
    {
        static::created(function ($user) {
            if ($user->management === 'shipper') {
                $user->createShipperProfile();
            }
        });
    }

    /** ────────────────────────────────
     *  CREATE SHIPPER PROFILE FUNCTION
     *  ──────────────────────────────── */
    public function createShipperProfile(): void
    {
        // Avoid duplicate creation if it already exists
        if ($this->shipper()->exists()) {
            return;
        }

        \App\Models\Shipper::create([
            'id' => $this->id, // optional if same id structure
            'management' => 'Normal Shipper',
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'national_id' => $this->national_id,
            'city_id' => $this->city_id ?? 1, // fallback default city
            'address' => $this->address,
            'password' => $this->password,
            'branch_id' => $this->branch_id ?? 1, // fallback branch
            'gender' => $this->gender ?? 'male',
        ]);
    }
}
