<?php
namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Enums\TransactionTypeEnum;
use App\Filament\Helpers\DatasetHelper;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

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

        foreach ($types as $type) {
            $filterQuery = $query->clone()
                ->selectRaw('SUM(value) as sum')
                ->where('type', $type['id'])
                ->get();
            $arr = $filterQuery->toArray();
            if($type['id'] == TransactionTypeEnum::EXPENSE->value) {
                $expense_sum = $arr[0]['sum'];
            } else {
                $income_sum = $arr[0]['sum'];
            }

            $dataset[] = Stat::make($type['title'], DatasetHelper::numberStringify($arr[0]['sum'] ?? 0))->color('text-red-600');
        }

        $dataset[] = Stat::make(__('Total'), DatasetHelper::numberStringify($income_sum - $expense_sum));

        return $dataset;
    }
}
