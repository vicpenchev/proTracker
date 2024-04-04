<?php

namespace App\Models;

use App\Traits\BootedModelUserActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'merge_fields' => 'json',
        'rules' => 'json',
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

    public function rule_fields() : BelongsToMany
    {
        return $this->belongsToMany(RuleField::class, 'rule_field_rule')->withTimestamps();
    }

    public function rule_groups() : BelongsToMany
    {
        return $this->belongsToMany(RuleGroup::class, 'rule_group_rule')->withTimestamps();
    }

    public function related_ruleFields() : BelongsToMany
    {
        return $this->belongsToMany(RuleField::class, 'rule_field_rule', 'rule_id', 'rule_field_id')->withTimestamps();
    }
}
