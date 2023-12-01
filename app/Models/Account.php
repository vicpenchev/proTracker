<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'user_id',
        'currency_id',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transactions() : HasMany
    {
        return $this->hasMany(Transaction::class, 'account_id', 'id');
    }

    public function currency() : BelongsTo
    {
        return $this->BelongsTo(Currency::class, 'currency_id', 'id');
    }
}
