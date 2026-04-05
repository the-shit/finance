<?php

namespace TheShit\Finance\Tools\Concerns;

use Carbon\Carbon;

trait ResolvePeriod
{
    /**
     * Resolve a period string into a [from, to] Carbon pair.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected static function resolvePeriod(string $period): array
    {
        return match ($period) {
            'last_month'  => [
                Carbon::now()->startOfMonth()->subMonthNoOverflow(),
                Carbon::now()->subMonthNoOverflow()->endOfMonth(),
            ],
            'last_30_days' => [
                Carbon::now()->subDays(30)->startOfDay(),
                Carbon::now()->endOfDay(),
            ],
            default => [ // current_month
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfDay(),
            ],
        };
    }
}
