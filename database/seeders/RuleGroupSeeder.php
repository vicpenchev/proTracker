<?php

namespace Database\Seeders;

use App\Enums\RuleFieldTypeEnum;
use App\Models\RuleGroup;
use Illuminate\Database\Seeder;

class RuleGroupSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $rules_groups_array = [
            [
                'title' => 'Pro Tracker 1 Group Rule',
                'user_id' => 1,
                'rules' => [1,2,3,4,5,6,7,8,9],
                'description' => 'Pro Tracker 1 Group Rule'
            ]
        ];

        foreach ($rules_groups_array as $rule_group) {
            $tmp_rule_array = $rule_group;
            unset($tmp_rule_array['rules']);
            $ruleGroupObject = RuleGroup::create($tmp_rule_array);
            $i = 1;
            foreach ($rule_group['rules'] as $rule) {
                $ruleGroupObject->related_rules_create()->attach($rule, ['order' => $i]);
                $i++;
            }
        }
    }
}
