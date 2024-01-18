<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

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

    /**
     * @return void
     */
    protected static function booted() : void
    {
        static::addGlobalScope('by_user', function (Builder $builder) {
            if(auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }

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

    public static function getForm(): array
    {
        return [
            TextInput::make('title')
                ->required()
                ->maxLength(255),
            Select::make('currency_id')
                ->relationship('currency', 'code')
                ->required()
                ->searchable()
                ->preload(),
        ];
    }
}
