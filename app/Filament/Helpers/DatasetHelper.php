<?php
namespace App\Filament\Helpers;

use App\Enums\TransactionTypeEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

final class DatasetHelper {
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
                    'value' => $grp->sum('value')
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
