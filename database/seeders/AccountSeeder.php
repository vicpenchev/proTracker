<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Account::insert(
            [
                [
                    'title' => 'Pro Tracker 1 Account 1 Credit Card',
                    'user_id' => 1,
                    'currency_id' => 34
                ],
                [
                    'title' => 'Pro Tracker 1 Account 2 Debit Card',
                    'user_id' => 1,
                    'currency_id' => 34
                ],
                [
                    'title' => 'Pro Tracker 1 Account 3 Credit Card New',
                    'user_id' => 1,
                    'currency_id' => 34
                ],
                [
                    'name' => 'Pro Tracker 2 Account 1 Credit Card',
                    'user_id' => 2,
                    'currency_id' => 108
                ],
            ]);
    }
}
