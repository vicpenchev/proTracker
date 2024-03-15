<?php

namespace Database\Seeders;

use App\Models\RuleField;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            UserSeeder::class,
            AccountSeeder::class,
            CategorySeeder::class,
            TransactionSeeder::class,
            RuleFieldsSeeder::class,
            RuleSeeder::class,
            RuleGroupSeeder::class,
        ]);
    }
}
