<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'country', 'currency', 'email_verified_at'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function creatorProfile(): HasOne { return $this->hasOne(CreatorProfile::class); }
    public function campaigns(): HasMany { return $this->hasMany(Campaign::class, 'brand_id'); }
    public function invitations(): HasMany { return $this->hasMany(Invitation::class, 'brand_id'); }

    public function toApiArray(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'email' => $this->email,
            'role' => $this->role, 'country' => $this->country, 'currency' => $this->currency];
    }
}
