<?php

namespace App\Models;

use App\Traits\BootedModelUserActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rule extends Model
{
    use HasFactory, BootedModelUserActions;

    /**
     * The $fillable array contains the list of attributes that are mass assignable.
     * These attributes can be assigned using the `create` method or by using mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'user_id',
        'type',
        'transaction_type',
        'category_id',
        'rules',
        'rule_fields',
        'merge_fields',
    ];

    protected $casts = [
        'rule_fields' => 'array',
        'merge_fields' => 'array',
        'rules' => 'array',
    ];

    /**
     * Retrieve the user associated with this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
