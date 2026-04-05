<?php

namespace TheShit\Finance\Tools;

use EchoLabs\Prism\Tool;
use TheShit\Finance\Contracts\FinanceDataProvider;
use TheShit\Finance\Privacy\PrivacyTransformer;
use TheShit\Finance\Tools\Concerns\ResolvePeriod;

class GetSpendingSummaryTool
{
    use ResolvePeriod;

    public static function make(): Tool
    {
        $provider    = app(FinanceDataProvider::class);
        $transformer = app(PrivacyTransformer::class);

        return Tool::as('get_spending_summary')
            ->for('Get a breakdown of spending by category for a given period. Use this to answer questions about where money is being spent.')
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
                    'Summarize spending by category — totals, counts, and any notable patterns.'
                );

                return $payload->toJson();
            });
    }
}
