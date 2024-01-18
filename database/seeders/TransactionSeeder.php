<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $months = 24;
        $acc_ids = [1,2,3];
        $transactions_income_arr = [];
        $transactions_expense_arr = [];
        $faker = Factory::create();
        foreach($acc_ids as $acc_id) {
            for ($i = 1; $i <= $months; $i++) {
                $date = Carbon::parse(now())->subMonthsNoOverflow($i);

                $transactions_income_arr[] = [
                    'account_id' => $acc_id,
                    'value' => $faker->randomFloat(2, 2000, 5000),
                    'date' => $date,
                    'from_acc' => Str::random(10),
                    'to_acc' => Str::random(10),
                    'notes' => 'FakeIncome',
                    'type' => 2,
                ];

                /*
                 * Food = 1
                 * Taxes and Bills = 2
                 * Gasoline = 3
                 * Buffer = 4
                 * Credit = 5
                 * Vacation = 6
                 * */

                $days_in_month = $date->daysInMonth;
                //Food
                $food_iteration = 20;
                $date_clone = $date->clone()->format('m/Y');
                for ($j = 0; $j < $food_iteration; $j++) {
                    $random_day = rand(1, $days_in_month);
                    $expense_date = Carbon::createFromFormat('d/m/Y', $random_day . '/' . $date_clone);
                    $transactions_expense_arr[] = $this->generateExpense($acc_id, 1, 100, $expense_date);
                }
                //Taxes and Bills
                $taxes_iteration = 10;
                for ($j = 0; $j < $taxes_iteration; $j++) {
                    $random_day = rand(1, $days_in_month);
                    $expense_date = Carbon::createFromFormat('d/m/Y', $random_day . '/' . $date_clone);
                    $transactions_expense_arr[] = $this->generateExpense($acc_id, 2, 60, $expense_date);
                }
                //Gasoline
                $gasoline_iteration = 5;
                for ($j = 0; $j < $gasoline_iteration; $j++) {
                    $random_day = rand(1, $days_in_month);
                    $expense_date = Carbon::createFromFormat('d/m/Y', $random_day . '/' . $date_clone);
                    $transactions_expense_arr[] = $this->generateExpense($acc_id, 3, 150, $expense_date);
                }
                //Buffer
                $buffer_iteration = 10;
                for ($j = 0; $j < $buffer_iteration; $j++) {
                    $random_day = rand(1, $days_in_month);
                    $expense_date = Carbon::createFromFormat('d/m/Y', $random_day . '/' . $date_clone);
                    $transactions_expense_arr[] = $this->generateExpense($acc_id, 4, 70, $expense_date);
                }
                //Credit
                $credit_iteration = 1;
                for ($j = 0; $j < $credit_iteration; $j++) {
                    $random_day = rand(1, $days_in_month);
                    $expense_date = Carbon::createFromFormat('d/m/Y', $random_day . '/' . $date_clone);
                    $transactions_expense_arr[] = $this->generateExpense($acc_id, 5, 600, $expense_date);
                }
                //Vacation
                $vacation_iteration = 1;
                for ($j = 0; $j < $vacation_iteration; $j++) {
                    $random_day = rand(1, $days_in_month);
                    $expense_date = Carbon::createFromFormat('d/m/Y', $random_day . '/' . $date_clone);
                    $transactions_expense_arr[] = $this->generateExpense($acc_id, 6, 300, $expense_date);
                }
            }
            //create income transactions for tha last $months
            $chunks = array_chunk($transactions_income_arr, 1000);
            foreach ($chunks as $chunk) {
                Transaction::insert($chunk);
            }
            //create expense transactions for tha last $months
            $chunks = array_chunk($transactions_expense_arr, 1000);
            foreach ($chunks as $chunk) {
                Transaction::insert($chunk);
            }
        }
    }

    private function generateExpense(int $accountId, int $categoryId, int $max_value, Carbon $date): array
    {
        $faker = Factory::create();

        return [
            'account_id' => $accountId,
            'category_id' => $categoryId,
            'value' => $faker->randomFloat(2, 1, $max_value),
            'date' => $date,
            'from_acc' => Str::random(10),
            'to_acc' => Str::random(10),
            'notes' => 'FakeExpense ' . $faker->words(5, true),
            'type' => 1,
        ];
    }
}
