<?php

namespace Database\Seeders;

use App\Enums\RuleFieldTypeEnum;
use App\Models\RuleField;
use Illuminate\Database\Seeder;

class RuleFieldsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        RuleField::insert([
            [
                'title' => 'Date/Time',
                'description' => 'Date/Time field',
                'user_id' => 1,
                'type' => RuleFieldTypeEnum::DATE->value,
            ],
            [
                'title' => 'Sum',
                'description' => 'Sum field',
                'user_id' => 1,
                'type' => RuleFieldTypeEnum::VALUE->value,
            ],
            [
                'title' => 'Type',
                'description' => 'Type field',
                'user_id' => 1,
                'type' => RuleFieldTypeEnum::TEXT->value,
            ],
            [
                'title' => 'Originator',
                'description' => 'Originator field',
                'user_id' => 1,
                'type' => RuleFieldTypeEnum::TEXT->value,
            ],
            [
                'title' => 'Recipient',
                'description' => 'Recipient field',
                'user_id' => 1,
                'type' => RuleFieldTypeEnum::TEXT->value,
            ],
            [
                'title' => 'Trsaction Description',
                'description' => 'Trsaction Description field',
                'user_id' => 1,
                'type' => RuleFieldTypeEnum::TEXT->value,
            ],
            [
                'title' => 'Additional Description',
                'description' => 'Additional Description field',
                'user_id' => 1,
                'type' => RuleFieldTypeEnum::TEXT->value,
            ],
            [
                'title' => 'Test field Pro Tracker 2',
                'description' => 'Test field Pro Tracker 2 field',
                'user_id' => 2,
                'type' => RuleFieldTypeEnum::TEXT->value,
            ],
        ]);
    }
}
