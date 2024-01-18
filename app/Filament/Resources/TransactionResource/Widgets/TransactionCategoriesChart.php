<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Filament\Helpers\DatasetHelper;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Category;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Illuminate\Support\Carbon;

class TransactionCategoriesChart extends ChartWidget
{
    use InteractsWithPageTable;

    protected static ?string $heading = 'Transactions by Category';

    protected static string $color = 'info';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'monthly12';

    protected static ?string $pollingInterval = null;

    public ?string $dataChecksum = '';

    protected function getFilters(): ?array
    {
        return [
            'monthly6' => 'Monthly (Last 6 months with data)',
            'monthly12' => 'Monthly (Last 12 months with data)',
            'monthly24' => 'Monthly (Last 24 months with data)',
            'monthly36' => 'Monthly (Last 36 months with data)',
            'yearly5' => 'Yearly (Last 5 years with data)',
            'yearly10' => 'Yearly (Last 10 years with data)',
            'yearly15' => 'Yearly (Last 15 years with data)',
            'yearly20' => 'Yearly (Last 20 years with data)',
            //'all_time' => 'All Data',
        ];
    }

    protected function getTablePage(): string
    {
        return ListTransactions::class;
    }

    protected function getData(): array
    {
        $filter = $this->filter;
        $start_date = Carbon::now()->subYearsNoOverflow(1);
        $end_date = Carbon::now();

        $query = $this->getPageTableQuery()->clone();
        if($query) {
            $query->getQuery()->orders = [];
            $collection = collect($query->get());
        } else {
            $collection = collect([]);
        }
        if($collection->count()) {
            $start_date = Carbon::make($collection->sortBy('date')->first()->date);
            $end_date = Carbon::make($collection->sortBy('date', SORT_REGULAR, true)->first()->date);
        }
        //dd($end_date);
        //Log::info('Start Date: ' . $start_date);
        //Log::info('End Date: ' . $end_date);

        $categories = Category::all()->map(
            function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'color' => $item->color,
                    ];
            })->toArray();

        //dd($categories);
        $dataset = [];
        $chart_x_categories = [];
        switch ($filter) {
            case 'monthly36' :
            case 'monthly24' :
            case 'monthly12' :
            case 'monthly6' :
                //get data for the past x months
                $interval = 5;
                if($filter == 'monthly12') {
                    $interval = 11;
                } elseif ($filter == 'monthly24') {
                    $interval = 23;
                } elseif ($filter == 'monthly36') {
                    $interval = 35;
                }
                $index = 0;
                $category_date = Carbon::now()->format('m/Y');
                for($i = $interval; $i >= 0; $i--) {
                    $category_date = Carbon::parse($end_date)->subMonthsNoOverflow($i)->format('m/Y');
                    $chart_x_categories[$index] = str($category_date);
                    $index++;
                }

                $start_date = Carbon::createFromFormat('d/m/Y' , '01/' . Carbon::parse($end_date)->subMonthsNoOverflow($interval)->format('m/Y'));

                foreach ($categories as $category) {
                    $dataset[] = DatasetHelper::createDatasetItem('category_id', $category['id'], $category['title'], $start_date, $end_date, $query, 'perMonth', $category['color'], $interval);
                }
                //Log::info(print_r($dataset, true));
                break;
            case 'yearly5' :
            case 'yearly10' :
            case 'yearly15' :
            case 'yearly20' :
                //get data for the past x months
                $interval = 4;
                if($filter == 'yearly10') {
                    $interval = 9;
                } elseif ($filter == 'yearly15') {
                    $interval = 14;
                } elseif ($filter == 'yearly20') {
                    $interval = 19;
                }
                $index = 0;
                $category_date = Carbon::now()->format('Y');
                for($i = $interval; $i >= 0; $i--) {
                    $category_date = Carbon::parse($end_date)->subYears($i)->format('Y');
                    $chart_x_categories[$index] = str($category_date);
                    $index++;
                }

                $start_date = Carbon::createFromFormat('d/m/Y' , '01/01/' . Carbon::parse($end_date)->subYearsNoOverflow($interval)->format('Y'));

                foreach ($categories as $category) {
                    $dataset[] = DatasetHelper::createDatasetItem('category_id', $category['id'], $category['title'], $start_date, $end_date, $query, 'perYear', $category['color'], $interval);
                }

                break;
        }

        return [
            'datasets' => $dataset,
            'labels' => $chart_x_categories,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
