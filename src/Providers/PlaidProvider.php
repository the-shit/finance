<?php

namespace TheShit\Finance\Providers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use TheShit\Finance\Contracts\FinanceDataProvider;
use TheShit\Finance\Contracts\SyncResult;
use TheShit\Finance\Plaid\DTOs\Account;
use TheShit\Finance\Plaid\DTOs\Transaction;
use TheShit\Finance\Plaid\PlaidConnector;
use TheShit\Finance\Plaid\Requests\GetAccounts;
use TheShit\Finance\Plaid\Requests\SyncTransactions;

class PlaidProvider implements FinanceDataProvider
{
    public function __construct(
        private readonly PlaidConnector $connector,
        private readonly string $accessToken,
    ) {}

    public function accounts(): Collection
    {
        $response = $this->connector->send(new GetAccounts($this->accessToken));

        return collect($response->json('accounts'))
            ->map(fn (array $account) => Account::fromPlaid($account));
    }

    public function transactions(Carbon $from, Carbon $to): Collection
    {
        $all    = collect();
        $cursor = null;

        do {
            $result = $this->sync($cursor);
            $all    = $all->merge($result->added);
            $cursor = $result->nextCursor;
        } while ($result->hasMore);

        return $all->filter(
            fn (Transaction $t) => $t->date->between($from, $to)
        )->values();
    }

    public function sync(?string $cursor = null): SyncResult
    {
        $response = $this->connector->send(
            new SyncTransactions($this->accessToken, $cursor)
        );

        $data = $response->json();

        return new SyncResult(
            added:      collect($data['added'])->map(fn ($t) => Transaction::fromPlaid($t)),
            modified:   collect($data['modified'])->map(fn ($t) => Transaction::fromPlaid($t)),
            removed:    collect($data['removed']),
            nextCursor: $data['next_cursor'],
            hasMore:    $data['has_more'],
        );
    }
}
