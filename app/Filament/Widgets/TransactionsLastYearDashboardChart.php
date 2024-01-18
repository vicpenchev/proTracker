<?php

namespace App\Filament\Widgets;

use App\Filament\Helpers\DatasetHelper;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TransactionsLastYearDashboardChart extends ChartWidget
{
    protected static ?string $heading = 'Transactions This Year';

    protected int | string | array $columnSpan = 1;

    protected static ?string $pollingInterval = null;

    public ?string $dataChecksum = '';

    protected function getData(): array
    {
        $start_date = Carbon::now()->startOfYear();
        $end_date = Carbon::now()->endOfYear();

        $categories = DatasetHelper::generateCategoriesFor();

        $dataset = [];
        $chart_x_categories = array_map(fn($month) => Carbon::create(null, $month)->format('F'), range(1, 12));

        $query = Transaction::query();

        foreach ($categories as $category) {
            $dataset[] = DatasetHelper::createDatasetItem('type', $category['id'], $category['title'], $start_date, $end_date, $query, 'perMonth', $category['color'], 11);
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
