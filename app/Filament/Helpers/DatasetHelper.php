<?php
namespace App\Filament\Helpers;

use App\Enums\TransactionTypeEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

final class DatasetHelper {

    /**
     * Create a dataset item for a chart
     *
     * @param string $fieldName The field name to filter on
     * @param int $id The ID to filter on
     * @param string $title The title of the dataset item
     * @param string $start_date The start date for the filter
     * @param string $end_date The end date for the filter
     * @param object $query The query object to filter on
     * @param string $timeFrame The time frame to group the data (perDay, perMonth, perYear)
     * @param string $color The color of the dataset item
     * @param int $interval The interval of the dataset item
     * @return array The dataset item array
     */
    static public function createDatasetItem($fieldName, $id, $title, $start_date, $end_date, $query, $timeFrame, $color, $interval): array
    {
        $filterQuery = $query->clone()
            ->whereBetween('date', [$start_date, $end_date])
            ->where($fieldName, $id)
            ->orderBy('date')
            ->get();

        $dataset_arr = [
            'label' => $title,
            'data' => array_map(fn($month) => 0, range(1,$interval+1)),
            'borderColor' => strtoupper($color),
            'backgroundColor' => strtoupper($color),
            'pointBorderColor' => strtoupper($color),
            'pointBackgroundColor' => strtoupper($color),
            'tension' => 0.5,
        ];

        if(!$filterQuery->count()) {
            return $dataset_arr;
        }

        //perMonth
        if($timeFrame == 'perDay') {
            $data = $filterQuery->groupBy(function ($data) {
                return Carbon::parse($data->date)->format('Y-m-d');
            });
        } elseif($timeFrame == 'perMonth') {
            $data = $filterQuery->groupBy(function ($data) {
                return Carbon::parse($data->date)->format('Y-m');
            });
        } else {
            $data = $filterQuery->groupBy(function ($data) {
                return Carbon::parse($data->date)->format('Y');
            });
        }

        $data = $data->mapWithKeys(function ($grp, $key){
            return [
                $key => [
                    'value' => $grp->sum((Auth::user()->stats_base_currency ? 'value_converted' : 'value'))
                ]
            ];
        });

        $data = $data->slice(-($interval+1));
        $data = $data->toArray();

        for($i = 0; $i <= $interval; $i++) {
            if ($timeFrame == 'perDay') {
                $category_date = Carbon::parse($end_date)->subDays($i)->format('Y-m-d');
                //dd($category_date);
            } elseif ($timeFrame == 'perMonth') {
                $category_date = Carbon::parse($end_date)->subMonthsNoOverflow($i)->format('Y-m');
                //dd($category_date);
            } else {
                $category_date = Carbon::parse($end_date)->subYearsNoOverflow($i)->format('Y');
            }
            if(!isset($data[$category_date])) {
                $data[$category_date] = 0;
            }
        }
        ksort($data);
        $data = collect($data);

        if($data->count() > 0) {
            $dataset_arr['data'] = $data->flatten()->toArray();
        }

        return $dataset_arr;
    }

    /**
     * Generates an array of categories.
     *
     * This method generates an array of category objects, where each object
     * represents a category in a transaction. The category objects contain the
     * following properties: 'id', 'title', and 'color'. The 'id' property represents
     * the value of the TransactionTypeEnum for the category. The 'title' property
     * represents the description of the category. The 'color' property represents
     * the color code associated with the category, which is either '#dc2626' for
     * expense categories or '#65a30d' for other categories.
     *
     * @return array An array of category objects.
     */
    static public function generateCategoriesFor(): array
    {
        $categories = [];
        $i = 0;
        foreach (TransactionTypeEnum::toArray() as $key => $value) {
            $categories[$i]['id'] = $value;
            $categories[$i]['title'] = $key;
            $categories[$i]['color'] = ($value == TransactionTypeEnum::EXPENSE->value) ? '#dc2626' : '#65a30d';
            $i++;
        }
        return $categories;
    }

    /**
     * Converts a number to a string representation.
     *
     * This method takes a number and converts it to a string representation using
     * a specified precision. By default, the precision is set to 2 decimal places.
     * The method handles numbers less than 1000 by directly formatting the number.
     * If the number is between 1000 and 999999, it is divided by 1000 and formatted
     * with the precision, followed by appending 'k'(thousands). For numbers
     * greater than 999999, the number is divided by 1000000, then divided by 1000
     * and formatted with the specified precision, followed by appending 'm'(million).
     *
     * @param int $number The number to be converted.
     * @param int $precision The number of decimal places to round the result to. Default is 2.
     * @return string The string representation of the number with the specified precision.
     */
    static public function numberStringify(int $number, int $precision = 2): string {
        $negative = 0;
        if($number < 0) {
            $negative = 1;
            $number = abs($number);
        }
        if ($number < 1000) {
            return Number::format(($negative ? -$number : $number), $precision);
        }

        if ($number < 1000000) {
            return Number::format(($negative ? -($number / 1000) : ($number / 1000)), $precision) . 'k';
        }

        return Number::format(($negative ? -($number / 1000) : ($number / 1000)) / 1000000, $precision) . 'm';
    }

    /**
     * Calculates the percentage change between two numbers.
     *
     * This method calculates the percentage change between two numbers, given the
     * old value and the new value. The percentage change is calculated using the
     * formula: ((new - old) / old) * 100.
     *
     * @param float $old The old value.
     * @param float $new The new value.
     * @param int $precision The number of decimal places to round the result to. Default is 2.
     * @return float The calculated percentage change.
     */
    static public function percent_change($old, $new, int $precision = 2): float
    {
        if ($old == 0) {
            $old++;
            $new++;
        }

        $change = (($new - $old) / $old) * 100;

        return round($change, $precision);
    }
}
