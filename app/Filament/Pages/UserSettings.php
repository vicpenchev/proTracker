<?php

namespace App\Filament\Pages;

use App\Jobs\ConvertToUserBaseCurrency;
use App\Models\Currency;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;

class UserSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.user-settings';
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'User Settings';
    protected static ?string $navigationGroup = 'Settings';

    public $currency_id;
    public $stats_base_currency;

    protected function getFormSchema(): array
    {
        return [
            Select::make('currency_id')
                ->label('Base Currency')
                ->options(Currency::all()->pluck('code', 'id')->prepend('None', null))
                ->preload()
                ->searchable()
                ->live()
                ->placeholder('No Base Currency Selected')
                ->default(fn () => Auth::user()->currency_id),
            Checkbox::make('stats_base_currency')
                ->visible(fn (Forms\Get $get): bool => ($get('currency_id') > 0))
                ->label('Use Base Currency for Stats')
                ->default(fn () => (boolean)Auth::user()->stats_base_currency),
                //->default(true),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();
        $user->currency_id = $data['currency_id'] ?? null;
        $user->stats_base_currency = $data['stats_base_currency'] ?? false;
        $user->save();

        $user->refresh();

        ConvertToUserBaseCurrency::dispatch($user);
        //ConvertToUserBaseCurrency::dispatch($user, 5);

        Notification::make()
            ->title('User Settings Updated')
            ->success()
            ->send();
    }

    public function mount(): void
    {
        $this->form->fill([
            'currency_id' => Auth::user()->currency_id,
            'stats_base_currency' => Auth::user()->stats_base_currency,
        ]);
    }
}
