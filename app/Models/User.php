<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'address', 'avatar', 'is_blocked',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_blocked'        => 'boolean',
        ];
    }

    // role: consumer | merchant | analyst
    public function isConsumer(): bool { return $this->role === 'consumer'; }
    public function isMerchant(): bool { return $this->role === 'merchant'; }
    public function isAnalyst(): bool  { return $this->role === 'analyst'; }

    public function products() { return $this->hasMany(Product::class, 'merchant_id'); }
    public function orders()   { return $this->hasMany(Order::class); }
    public function cart()     { return $this->hasOne(Cart::class); }
    public function companyProfile() { return $this->hasOne(CompanyProfile::class, 'merchant_id'); }
}
