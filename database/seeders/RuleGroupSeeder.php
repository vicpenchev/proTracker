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
        RuleGroup::insert([
            [
                'title' => 'Pro Tracker 1 Group Rule',
                'user_id' => 1,
                'rules' => '[{"rule":"1"},{"rule":"2"},{"rule":"3"},{"rule":"4"},{"rule":"5"},{"rule":"6"},{"rule":"7"},{"rule":"8"},{"rule":"9"}]',
                'description' => 'Pro Tracker 1 Group Rule'
            ]
        ]);
    }
}
