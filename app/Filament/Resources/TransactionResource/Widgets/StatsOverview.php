<?php
namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Enums\TransactionTypeEnum;
use App\Filament\Helpers\DatasetHelper;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Category;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListTransactions::class;
    }

    protected function getStats(): array
    {
        $value_column = (Auth::user()->stats_base_currency ? 'value_converted' : 'value');

        $query = $this->getPageTableQuery();

        $categories = Category::all()->map(
        function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'color' => $item->color,
            ];
        })->toArray();

        $dataset = [];

        $total_income_sum = 0;
        if($query->count()) {
            $filterQuery_total = $query->clone()
                ->selectRaw('SUM(' . $value_column . ') as sum')
                ->where('type', TransactionTypeEnum::INCOME->value)
                ->get();
            $arr_total = $filterQuery_total->toArray();
            $total_income_sum = $arr_total[0]['sum'];
        }

        foreach ($categories as $category) {
            $start_date = null;
            $end_date = null;
            $diffInDays = null;
            $diffInMonths = null;

            $query_clone = $query->clone();
            if($query_clone) {
                $collection = collect($query_clone->get());
            } else {
                $collection = collect([]);
            }
            if($collection->count()) {
                $start_date = Carbon::make($collection->sortBy('date')->first()->date);
                $end_date = Carbon::make($collection->sortBy('date', SORT_REGULAR, true)->first()->date);
                $diffInMonths = $start_date->diffInMonths($end_date);
                $diffInDays = $start_date->diffInDays($end_date);
                if($diffInMonths) {
                    $diffInMonths++;
                }
            }

            $filterQuery = $query->clone()
                ->selectRaw('SUM(' . $value_column . ') as sum')
                ->where('category_id', $category['id'])
                ->get();
            $arr = $filterQuery->toArray();

            $avg_sum_per_month = 0;
            $avg_sum_per_day = 0;
            $dscr = '';
            $percentage_per_month = '';
            if($diffInMonths && $arr[0]['sum']) {
                if ($total_income_sum) {
                    $percentage_per_month = Number::format(($arr[0]['sum'] / $total_income_sum) * 100, 2);
                }
                $dscr .= 'AVG: ';
                $avg_sum_per_month = Number::format($arr[0]['sum']/$diffInMonths, 2);
                $dscr .= $avg_sum_per_month . ' monthly';
            }
            if($diffInDays && $arr[0]['sum']) {
                $dscr .= (!$dscr ? 'AVG: ' : ' | ');
                $avg_sum_per_month = Number::format($arr[0]['sum']/$diffInDays, 2);
                $dscr .= $avg_sum_per_month . ' daily';
            }
            if($percentage_per_month) {
                $dscr .= (!$dscr ? 'AVG: ' : ' | ');
                $dscr .= $percentage_per_month . '%';
            }

            $dataset[] = Stat::make($category['title'], DatasetHelper::numberStringify($arr[0]['sum'] ?? 0))
                ->description($dscr);
                //->description(DatasetHelper::numberStringify(32589) . ' increase')
                /*->description(DatasetHelper::percent_change(2000, 2300.56) . '% MoM \n' . DatasetHelper::percent_change(45895, 59875) . '% YoY')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => "\$dispatch('setCategoryFilter', { filter: 1 })",
                ]);*/
        }

        return $dataset;
    }
}
