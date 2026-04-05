<?php

use Carbon\Carbon;
use TheShit\Finance\Tools\GetSpendingSummaryTool;

// Access the trait via a concrete tool class
beforeEach(fn () => Carbon::setTestNow('2026-04-15 12:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('resolves current_month to start of month through today', function () {
    // Use reflection to test the protected trait method
    $method = new ReflectionMethod(GetSpendingSummaryTool::class, 'resolvePeriod');
    $method->setAccessible(true);

    [$from, $to] = $method->invoke(null, 'current_month');

    expect($from->toDateString())->toBe('2026-04-01')
        ->and($to->toDateString())->toBe('2026-04-15');
});

it('resolves last_month to the full previous month', function () {
    $method = new ReflectionMethod(GetSpendingSummaryTool::class, 'resolvePeriod');
    $method->setAccessible(true);

    [$from, $to] = $method->invoke(null, 'last_month');

    expect($from->toDateString())->toBe('2026-03-01')
        ->and($to->toDateString())->toBe('2026-03-31');
});

it('resolves last_30_days to a rolling window', function () {
    $method = new ReflectionMethod(GetSpendingSummaryTool::class, 'resolvePeriod');
    $method->setAccessible(true);

    [$from, $to] = $method->invoke(null, 'last_30_days');

    expect($from->toDateString())->toBe('2026-03-16')
        ->and($to->toDateString())->toBe('2026-04-15');
});
