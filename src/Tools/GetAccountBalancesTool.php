<?php

namespace TheShit\Finance\Tools;

use EchoLabs\Prism\Tool;
use TheShit\Finance\Contracts\FinanceDataProvider;

class GetAccountBalancesTool
{
    public static function make(): Tool
    {
        $provider = app(FinanceDataProvider::class);

        return Tool::as('get_account_balances')
            ->for('Get current balances for all connected bank and credit accounts. Use this to answer questions about available funds, credit limits, or total net worth across accounts.')
            ->using(function () use ($provider): string {
                $accounts = $provider->accounts();

                // Balances are structural, not transactional — safe to summarize
                // without merchant-level PII. We round to nearest $10 for cloud.
                $summary = $accounts->map(fn ($account) => [
                    'type'      => $account->type,
                    'subtype'   => $account->subtype,
                    'available' => $account->available !== null
                        ? '$'.number_format(round($account->available / 10) * 10)
                        : null,
                    'current'   => '$'.number_format(round($account->current / 10) * 10),
                    'limit'     => $account->limit !== null
                        ? '$'.number_format($account->limit)
                        : null,
                ])->values()->toArray();

                return json_encode($summary, JSON_PRETTY_PRINT);
            });
    }
}
