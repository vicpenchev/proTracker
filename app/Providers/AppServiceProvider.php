<?php

namespace App\Providers;

use Carbon\Traits\Date;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        /*DatePicker::configureUsing(function (DatePicker $datePicker) {
            $datePicker->displayFormat('d/m/Y');
        });*/

        /*DateTimePicker::configureUsing(function (DateTimePicker $dateTimePicker) {
            $dateTimePicker->displayFormat('d/m/Y');
        });*/
    }
}
