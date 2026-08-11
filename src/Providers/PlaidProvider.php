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
use TheShit\Finance\Plaid\Requests\GetTransactions;
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
        $all = collect();
        $offset = 0;
        $batchSize = 500;
        $start = $from->toDateString();
        $end = $to->toDateString();

        do {
            $response = $this->connector->send(
                new GetTransactions($this->accessToken, $start, $end, $batchSize, $offset)
            );

            $batch = collect($response->json('transactions') ?? [])
                ->map(fn (array $t) => Transaction::fromPlaid($t));
            $all = $all->merge($batch);
            $total = (int) ($response->json('total_transactions') ?? $all->count());
            $offset += $batchSize;
        } while ($offset < $total);

        return $all->values();
    }

    public function sync(?string $cursor = null): SyncResult
    {
        $response = $this->connector->send(
            new SyncTransactions($this->accessToken, $cursor)
        );

        $data = $response->json();

        return new SyncResult(
            added: collect($data['added'] ?? [])->map(fn ($t) => Transaction::fromPlaid($t)),
            modified: collect($data['modified'] ?? [])->map(fn ($t) => Transaction::fromPlaid($t)),
            removed: collect($data['removed'] ?? []),
            nextCursor: $data['next_cursor'] ?? '',
            hasMore: (bool) ($data['has_more'] ?? false),
        );
    }
}
