<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::insert(
            [
                [
                    'name' => 'Pro Tracker 1',
                    'email' => 'protracker@protracker.test',
                    'password' => bcrypt('111111')
                ],
                [
                    'name' => 'Pro Tracker 2',
                    'email' => 'protracker2@protracker.test',
                    'password' => bcrypt('111111')
                ],
            ]);
    }
}
