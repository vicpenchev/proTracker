<?php


namespace App\Console\Commands;

use App\Interfaces\CurrencyExchangeRateInterface;
use App\Jobs\ConvertToUserBaseCurrency;
use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Console\Command;

class CurrencyExchangeRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency_rates:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh currency exchange rates';

    protected $exchangeRateService;

    public function __construct(CurrencyExchangeRateInterface $exchangeRateService) {
        parent::__construct();
        $this->exchangeRateService = $exchangeRateService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $refreshedCurrencies = $this->exchangeRateService->refreshExchangeRates();

            if (count($refreshedCurrencies)) {
                foreach ($refreshedCurrencies as $currency => $rate) {
                    $currencyId = Currency::where('code', $currency)->first()->id;
                    $usersData = User::where('currency_id', $currencyId)->get();
                    foreach ($usersData as $user) {
                        ConvertToUserBaseCurrency::dispatch($user);
                    }
                }
            }

            $this->info('Exchange rates fetched and stored successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to fetch exchange rates: ' . $e->getMessage());
        }
    }
}
