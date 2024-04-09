<?php

namespace App\Models;

use App\Traits\BootedModelUserActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RuleGroup extends Model
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
        'rules',
        'description',
    ];

    protected $casts = [
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

    public function rules() : HasMany
    {
        return $this->hasMany(RuleGroupPivot::class);
    }

    public function related_rules_create() : BelongsToMany
    {
        return $this->belongsToMany(Rule::class, 'rule_group_rule', 'rule_group_id', 'rule_id')
            ->withPivot('order')
            ->withTimestamps();
    }

    public function related_rules() : BelongsToMany
    {
        return $this->belongsToMany(Rule::class, 'rule_group_rule')->using(RuleGroupPivot::class)->orderByPivot('order');
    }
}
