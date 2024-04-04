<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RuleGroupPivot extends Pivot
{
    protected $table = 'rule_group_rule';

    public function rule() : BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }

    public function ruleGroup() : BelongsTo
    {
        return $this->belongsTo(RuleGroup::class);
    }
}
