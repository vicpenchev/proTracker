<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Category::insert([
            [
                'title' => 'Food',
                'user_id' => 1,
                'color' => '#ef4444',
                'description' => 'Food and Drinks'
            ],
            [
                'title' => 'Taxes and Bills',
                'user_id' => 1,
                'color' => '#b91c1c',
                'description' => "House, Taxes, Bills, Car, Insurances"
            ],
            [
                'title' => 'Gasoline',
                'user_id' => 1,
                'color' => '#991b1b',
                'description' => "Gasoline"
            ],
            [
                'title' => 'Buffer',
                'user_id' => 1,
                'color' => '#7f1d1d',
                'description' => "Exceptional costs"
            ],
            [
                'title' => 'Credit',
                'user_id' => 1,
                'color' => '#fb7185',
                'description' => 'credit installment'
            ],
            [
                'title' => 'Vacation',
                'user_id' => 1,
                'color' => '#86efac',
                'description' => 'Vacation / Excursion'
            ],
            [
                'title' => 'Category User Two 1',
                'user_id' => 2,
                'color' => '#bbf7d0',
                'description' => '',
            ],
            [
                'title' => 'Category User Two 2',
                'user_id' => 2,
                'color' => '#faa706',
                'description' => '',
            ],
        ]);
    }
}
