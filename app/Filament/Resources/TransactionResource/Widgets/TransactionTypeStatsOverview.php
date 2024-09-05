<?php
namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Enums\TransactionTypeEnum;
use App\Filament\Helpers\DatasetHelper;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class TransactionTypeStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    private bool $show = false;

    protected function getTablePage(): string
    {
        return ListTransactions::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();
        $types = DatasetHelper::generateCategoriesFor();
        $dataset = [];

        $expense_sum = 0;
        $income_sum = 0;

        $total_income_sum = 0;
        if($query->count()) {
            $filterQuery_total = $query->clone()
                ->selectRaw('SUM(value) as sum')
                ->where('type', TransactionTypeEnum::INCOME->value)
                ->get();
            $arr_total = $filterQuery_total->toArray();
            $total_income_sum = $arr_total[0]['sum'];
        }

        foreach ($types as $type) {
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

            if($type['id'] == TransactionTypeEnum::EXPENSE->value) {
                $filterQuery = $query->clone()
                    ->selectRaw('SUM(value) as sum')
                    ->where('type', $type['id'])
                    ->get();
                $arr = $filterQuery->toArray();
                $expense_sum = $arr[0]['sum'];
            } else {
                $arr[0]['sum'] = $total_income_sum;
                $income_sum = $total_income_sum;
            }

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

            $dataset[] = Stat::make($type['title'], DatasetHelper::numberStringify($arr[0]['sum'] ?? 0))
                ->description($dscr)
                ->color('text-red-600');
        }

        $dscr = $total_income_sum ? 'AVG: ' . Number::format((($income_sum - $expense_sum)/$total_income_sum) * 100, 2) . '%' : '';
        $dataset[] = Stat::make(__('Total'), DatasetHelper::numberStringify($income_sum - $expense_sum))
            ->description($dscr);

        return $dataset;
    }
}
