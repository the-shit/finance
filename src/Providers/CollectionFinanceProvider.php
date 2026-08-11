<?php

namespace TheShit\Finance\Providers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use TheShit\Finance\Contracts\FinanceDataProvider;
use TheShit\Finance\Contracts\SyncResult;
use TheShit\Finance\Plaid\DTOs\Account;
use TheShit\Finance\Plaid\DTOs\Transaction;

/**
 * In-memory / preloaded provider — tests, local ledgers, non-Plaid sources.
 */
final class CollectionFinanceProvider implements FinanceDataProvider
{
    /**
     * @param  Collection<int, Transaction>  $transactions
     * @param  Collection<int, Account>  $accounts
     */
    public function __construct(
        private Collection $transactions = new Collection,
        private Collection $accounts = new Collection,
    ) {}

    public function accounts(): Collection
    {
        return $this->accounts->values();
    }

    public function transactions(Carbon $from, Carbon $to): Collection
    {
        $fromDay = $from->copy()->startOfDay();
        $toDay = $to->copy()->endOfDay();

        return $this->transactions
            ->filter(function (Transaction $t) use ($fromDay, $toDay) {
                return $t->date->greaterThanOrEqualTo($fromDay)
                    && $t->date->lessThanOrEqualTo($toDay);
            })
            ->values();
    }

    public function sync(?string $cursor = null): SyncResult
    {
        return new SyncResult(
            added: collect(),
            modified: collect(),
            removed: collect(),
            nextCursor: $cursor ?? '',
            hasMore: false,
        );
    }
}
