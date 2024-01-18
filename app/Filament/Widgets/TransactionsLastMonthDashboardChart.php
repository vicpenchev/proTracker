<?php

namespace App\Filament\Widgets;

use App\Filament\Helpers\DatasetHelper;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TransactionsLastMonthDashboardChart extends ChartWidget
{
    protected static ?string $heading = 'Transactions Last Month';

    protected static string $color = 'warning';

    protected int | string | array $columnSpan = 1;

    public ?string $filter = 'yearly';

    protected static ?string $pollingInterval = '10s';

    public ?string $dataChecksum = '';

    protected function getData(): array
    {
        $start_date = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $end_date = Carbon::now()->subMonthNoOverflow()->endOfMonth();
        $days_in_month = $end_date->daysInMonth;

        $categories = DatasetHelper::generateCategoriesFor();

        $dataset = [];
        $chart_x_categories = [];
        $index = 0;
        $category_date = Carbon::now()->format('d/m/Y');

        for($i = ($days_in_month-1); $i >= 0; $i--) {
            $category_date = Carbon::parse($end_date)->subDays($i)->format('d/m/Y');
            $chart_x_categories[$index] = str($category_date);
            $index++;
        }

        $query = Transaction::query();

        foreach ($categories as $category) {
            $dataset[] = DatasetHelper::createDatasetItem('type', $category['id'], $category['title'], $start_date, $end_date, $query, 'perDay', $category['color'], ($days_in_month-1));
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
