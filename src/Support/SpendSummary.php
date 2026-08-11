<?php

namespace TheShit\Finance\Support;

use Illuminate\Support\Collection;
use TheShit\Finance\Plaid\DTOs\Transaction;

/**
 * Deterministic spend aggregation from provider transactions.
 * Plaid amount convention: positive = debit/outflow.
 */
final class SpendSummary
{
    /**
     * @param  Collection<int, Transaction>  $transactions
     * @return array{
     *   entry_count: int,
     *   total_outflow: string,
     *   total_inflow: string,
     *   net: string,
     *   by_category: list<array{category: string, total: string, count: int}>,
     *   as_of: string
     * }
     */
    public static function fromTransactions(Collection $transactions): array
    {
        $outflow = 0.0;
        $inflow = 0.0;
        /** @var array<string, array{total: float, count: int}> $byCat */
        $byCat = [];

        foreach ($transactions as $t) {
            if (! $t instanceof Transaction) {
                continue;
            }
            $amt = $t->amount;
            if ($amt > 0) {
                $outflow += $amt;
            } else {
                $inflow += abs($amt);
            }

            $cat = $t->category[0] ?? ($t->merchantName ?: $t->name ?: 'other');
            $cat = is_string($cat) && $cat !== '' ? $cat : 'other';
            if (! isset($byCat[$cat])) {
                $byCat[$cat] = ['total' => 0.0, 'count' => 0];
            }
            // Net by category in signed Plaid convention (debits positive)
            $byCat[$cat]['total'] += $amt;
            $byCat[$cat]['count']++;
        }

        $byCategory = [];
        foreach ($byCat as $category => $row) {
            $byCategory[] = [
                'category' => $category,
                'total' => number_format($row['total'], 2, '.', ''),
                'count' => $row['count'],
            ];
        }
        usort($byCategory, fn (array $a, array $b): int => abs((float) $b['total']) <=> abs((float) $a['total']));

        return [
            'entry_count' => $transactions->count(),
            'total_outflow' => number_format($outflow, 2, '.', ''),
            'total_inflow' => number_format($inflow, 2, '.', ''),
            'net' => number_format($outflow - $inflow, 2, '.', ''),
            'by_category' => $byCategory,
            'as_of' => now()->toIso8601String(),
        ];
    }
}
