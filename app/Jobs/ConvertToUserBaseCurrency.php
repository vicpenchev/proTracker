<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Filament\Helpers\CurrencyHelper;

class ConvertToUserBaseCurrency implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    protected $account_id;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user, int $accountId = null)
    {
        $this->user = $user;
        $this->account_id = $accountId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->account_id) {
            $accountData = $this->user->accounts()
                ->with('transactions')
                ->where('id', $this->account_id)
                ->get();
        } else {
            $accountData = $this->user->accounts()
                ->with('transactions')
                ->get();
        }

        foreach ($accountData as $account) {
            if ($this->user->currency_id !== $account->currency_id) {
                $account_transactions = $account->transactions;
                if ($account_transactions->count() > 0) {
                    foreach ($account_transactions as $account_transaction) {
                        if (is_null($this->user->currency_id)) {
                            $account_transaction->value_converted = $account_transaction->value;
                        } else {
                            $converted_amount = CurrencyHelper::convert(
                                $account_transaction->value,
                                $account->currency_id,
                                $this->user->currency_id
                            );
                            $account_transaction->value_converted = $converted_amount;
                        }
                    }
                    $account->transactions()->saveMany($account_transactions);
                }
            }
        }
    }
}
