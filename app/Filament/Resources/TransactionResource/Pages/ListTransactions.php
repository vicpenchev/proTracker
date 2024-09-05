<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Imports\TransactionImporter;
use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Support\Assets\Js;

class ListTransactions extends ListRecords
{
    use ExposesTableToWidgets;

    protected $listeners = ['UpdateSettingsForWidget' => '$refresh'];

    protected static string $resource = TransactionResource::class;

    private array $widgets = [];

    public function mount(): void
    {
        parent::mount();

        FilamentAsset::register([
            Js::make('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js'),
            Js::make('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.js'),
            Js::make('html2pdf', asset('js/custom/html2pdf.js')),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make('import')
                ->tooltip('Importing transactions for Account.')
                ->importer(TransactionImporter::class)
                ->chunkSize(1000),
            ExportAction::make()
                ->exports([
                    ExcelExport::make()
                        ->askForWriterType()
                        ->askForFilename()
                        ->fromTable()
                ]),
            Actions\Action::make('generatePDF')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->visible(true)
                ->extraAttributes([
                    'onclick' => 'javascript:generatePDF()',
                ]),
        ];
    }

    public function getHeaderWidgets(): array
    {
        $widgets = [];
        //$widgets[] = TransactionResource\Widgets\Settings::class;
        if(Session::has('transaction_settings')) {
            $settings = Session::get('transaction_settings');

            if(in_array('expense_income_stats', $settings['widgets'])) {
                $widgets[] = TransactionResource\Widgets\TransactionTypeStatsOverview::class;
            }

            if(in_array('category_stats', $settings['widgets'])) {
                $widgets[] = TransactionResource\Widgets\StatsOverview::class;
            }

            if(in_array('category_chart', $settings['widgets'])) {
                $widgets[] = TransactionResource\Widgets\TransactionCategoriesChart::class;
            }

            if(in_array('expense_income_chart', $settings['widgets'])) {
                $widgets[] = TransactionResource\Widgets\TransactionsChart::class;
            }
        } else {
            $widgets = [...$widgets,
               // TransactionResource\Widgets\Settings::class,
                TransactionResource\Widgets\TransactionTypeStatsOverview::class,
                TransactionResource\Widgets\StatsOverview::class,
                TransactionResource\Widgets\TransactionCategoriesChart::class,
                TransactionResource\Widgets\TransactionsChart::class,
            ];
        }
        return $widgets;
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(Transaction::query()->count()),
            'published' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('published', true))
                ->badge(Transaction::query()->where('published', '=', true)->count()),
            'draft' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('published', false))
                ->badge(Transaction::query()->where('published', '=', false)->count()),
        ];
    }


}
