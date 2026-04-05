<?php

namespace TheShit\Finance\Tools;

use Prism\Prism\Tool;
use TheShit\Finance\Contracts\FinanceDataProvider;
use TheShit\Finance\Privacy\PrivacyTransformer;
use TheShit\Finance\Tools\Concerns\ResolvePeriod;

class GetCashflowTool
{
    use ResolvePeriod;

    public static function make(): Tool
    {
        $provider    = app(FinanceDataProvider::class);
        $transformer = app(PrivacyTransformer::class);

        return Tool::as('get_cashflow')
            ->for('Get income vs expenses for a period. Use this to answer questions about whether spending exceeds income, savings rate, or overall financial health.')
            ->withStringParameter(
                name:        'period',
                description: 'The time period to analyze. Options: current_month, last_month, last_30_days. Defaults to current_month.',
                required:    false,
            )
            ->using(function (string $period = 'current_month') use ($provider, $transformer): string {
                [$from, $to] = self::resolvePeriod($period);

                $transactions = $provider->transactions($from, $to)
                    ->filter(fn ($t) => ! $t->pending);

                $payload = $transformer->prepare(
                    $transactions,
                    'Calculate net cashflow: total income (credits) vs total expenses (debits). Include category breakdown for expenses.'
                );

                return $payload->toJson();
            });
    }
}
