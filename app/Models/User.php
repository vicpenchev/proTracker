<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    //use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'currency_id',
        'stats_base_currency',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'stats_base_currency' => 'boolean',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasVerifiedEmail() && $this->active;
    }

    public function accounts() : HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function categories() : HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function rules() : HasMany
    {
        return $this->hasMany(Rule::class);
    }

    public function currency() : HasOne
    {
        return $this->hasOne(Currency::class);
    }
}
