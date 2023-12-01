<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'label_id',
        'value',
        'date',
        'from_acc',
        'to_acc',
        'notes',
        'published'
    ];

    public function account() : BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function label() : BelongsTo
    {
        return $this->belongsTo(Label::class, 'label_id', 'id');
    }
}
