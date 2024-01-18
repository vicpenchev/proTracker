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
                    'name' => 'User One',
                    'email' => 'user_one@admin.com',
                    'password' => bcrypt('111111')
                ],
                [
                    'name' => 'User Two',
                    'email' => 'user_two@admin.com',
                    'password' => bcrypt('111111')
                ],
            ]);
    }
}
