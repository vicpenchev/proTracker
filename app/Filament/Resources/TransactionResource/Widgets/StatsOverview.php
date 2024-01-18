<?php
namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Filament\Helpers\DatasetHelper;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Category;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListTransactions::class;
    }

    protected function getStats(): array
    {
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

        foreach ($categories as $category) {
            $filterQuery = $query->clone()
                ->selectRaw('SUM(value) as sum')
                ->where('category_id', $category['id'])
                ->get();
            $arr = $filterQuery->toArray();
            $dataset[] = Stat::make($category['title'], DatasetHelper::numberStringify($arr[0]['sum'] ?? 0));
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
