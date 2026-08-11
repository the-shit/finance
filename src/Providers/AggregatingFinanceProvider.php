<?php

namespace TheShit\Finance\Providers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use TheShit\Finance\Contracts\FinanceDataProvider;
use TheShit\Finance\Contracts\SyncResult;

/**
 * Merge multiple FinanceDataProviders (e.g. one Plaid item per access token).
 */
final class AggregatingFinanceProvider implements FinanceDataProvider
{
    /**
     * @param  list<FinanceDataProvider>  $providers
     */
    public function __construct(
        private readonly array $providers,
    ) {}

    public function accounts(): Collection
    {
        return collect($this->providers)
            ->flatMap(fn (FinanceDataProvider $p) => $p->accounts())
            ->values();
    }

    public function transactions(Carbon $from, Carbon $to): Collection
    {
        return collect($this->providers)
            ->flatMap(fn (FinanceDataProvider $p) => $p->transactions($from, $to))
            ->values();
    }

    public function sync(?string $cursor = null): SyncResult
    {
        $added = collect();
        $modified = collect();
        $removed = collect();

        foreach ($this->providers as $provider) {
            $result = $provider->sync($cursor);
            $added = $added->merge($result->added);
            $modified = $modified->merge($result->modified);
            $removed = $removed->merge($result->removed);
        }

        return new SyncResult(
            added: $added->values(),
            modified: $modified->values(),
            removed: $removed->values(),
            nextCursor: $cursor ?? '',
            hasMore: false,
        );
    }
}
