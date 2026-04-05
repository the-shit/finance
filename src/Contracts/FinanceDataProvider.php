<?php

namespace TheShit\Finance\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use TheShit\Finance\Plaid\DTOs\Account;
use TheShit\Finance\Plaid\DTOs\Transaction;

interface FinanceDataProvider
{
    /**
     * All accounts with current balances.
     */
    public function accounts(): Collection;

    /**
     * Transactions for a given period.
     */
    public function transactions(Carbon $from, Carbon $to): Collection;

    /**
     * Sync new transactions since last cursor (Plaid sync API).
     * Returns added, modified, removed.
     */
    public function sync(?string $cursor = null): SyncResult;
}
