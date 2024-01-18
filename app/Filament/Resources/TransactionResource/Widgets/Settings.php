<?php
namespace App\Filament\Resources\TransactionResource\Widgets;

use Filament\Forms\Components\Actions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Facades\Session;

class Settings extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.transactions.settings';

    protected string $heading = 'Settings and Actions';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public ?array $data = [];

    private array $options = [
        'expense_income_stats' => 'Expense/Income Stats',
        'category_stats' => 'Category Stats',
        'category_chart' => 'Category Chart',
        'expense_income_chart' => 'Expense/Income Chart'
    ];

    private array $defaultSelectedOptions = [
      'widgets' => [
          'expense_income_stats',
          'category_stats',
          'category_chart',
          'expense_income_chart',
      ]
    ];

    public function mount(): void
    {
        $this->form->fill(Session::get('transaction_settings') ?? $this->defaultSelectedOptions);
    }

    public function settings(): void
    {
        Session::put('transaction_settings', $this->form->getState());
        Log::info(Session::get('transaction_settings'));
        $this->dispatch('UpdateSettingsForWidget', $this->form->getState());
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Grid::make()
                ->schema([
                    CheckboxList::make('widgets')
                        ->label('Show/Hide Widgets')
                        ->options($this->options)
                        /*->extraAttributes([
                            'wire:change' => "settings()"
                        ])*/,
                    Actions::make([
                        Action::make('apply')
                            ->label('Apply')
                            ->action(function () {
                                $this->settings();
                            }),
                        Action::make('save')
                            ->label('Save Settings')
                            /*->action(function () {
                                $this->settings();
                            })*/
                    ])->columnSpanFull(),
                ])
            ]);
    }
}
