<?php

use Carbon\Carbon;
use Prism\Prism\Tool;
use TheShit\Finance\Contracts\FinanceDataProvider;
use TheShit\Finance\Plaid\DTOs\Transaction;
use TheShit\Finance\Privacy\CloudPayload;
use TheShit\Finance\Privacy\PrivacyTransformer;
use TheShit\Finance\Tools\GetSpendingSummaryTool;

beforeEach(function () {
    Carbon::setTestNow('2026-04-15 12:00:00');

    $this->mockTransactions = collect([
        Transaction::fromPlaid([
            'transaction_id'    => 'txn_1',
            'account_id'        => 'acc_1',
            'amount'            => 50.00,
            'iso_currency_code' => 'USD',
            'name'              => 'Grocery Store',
            'date'              => '2026-04-05',
            'category'          => ['Food and Drink', 'Groceries'],
            'payment_channel'   => 'in store',
            'pending'           => false,
        ]),
    ]);

    $this->mockProvider = Mockery::mock(FinanceDataProvider::class);
    $this->mockTransformer = Mockery::mock(PrivacyTransformer::class);

    app()->instance(FinanceDataProvider::class, $this->mockProvider);
    app()->instance(PrivacyTransformer::class, $this->mockTransformer);
});

afterEach(fn () => Carbon::setTestNow());

it('returns a Tool instance with correct name', function () {
    $this->mockProvider->shouldReceive('transactions')->andReturn($this->mockTransactions);
    $this->mockTransformer->shouldReceive('prepare')->andReturn(
        new CloudPayload('test', ['Groceries' => 3])
    );

    $tool = GetSpendingSummaryTool::make();

    expect($tool)->toBeInstanceOf(Tool::class)
        ->and($tool->name())->toBe('get_spending_summary');
});

it('filters pending transactions before sending to transformer', function () {
    $withPending = collect([
        Transaction::fromPlaid([
            'transaction_id'    => 'txn_pending',
            'account_id'        => 'acc_1',
            'amount'            => 30.00,
            'iso_currency_code' => 'USD',
            'name'              => 'Pending Charge',
            'date'              => '2026-04-10',
            'category'          => ['Shopping'],
            'payment_channel'   => 'online',
            'pending'           => true,
        ]),
        ...$this->mockTransactions,
    ]);

    $this->mockProvider->shouldReceive('transactions')->andReturn($withPending);
    $this->mockTransformer
        ->shouldReceive('prepare')
        ->withArgs(fn ($txns) => $txns->count() === 1) // pending filtered out
        ->andReturn(new CloudPayload('test', []));

    $tool = GetSpendingSummaryTool::make();
    $tool->handle('current_month');
});

it('returns json string from cloud payload', function () {
    $this->mockProvider->shouldReceive('transactions')->andReturn($this->mockTransactions);
    $this->mockTransformer->shouldReceive('prepare')->andReturn(
        new CloudPayload('spending summary', ['Groceries' => ['count' => 1]])
    );

    $tool   = GetSpendingSummaryTool::make();
    $result = $tool->handle('current_month');

    expect($result)->toBeString()
        ->and(json_decode($result, true))->toHaveKey('data');
});

it('defaults to current_month when no period given', function () {
    $this->mockProvider
        ->shouldReceive('transactions')
        ->withArgs(fn ($from, $to) =>
            $from->toDateString() === '2026-04-01' &&
            $to->toDateString() === '2026-04-15'
        )
        ->andReturn(collect());

    $this->mockTransformer->shouldReceive('prepare')->andReturn(
        new CloudPayload('test', [])
    );

    $tool = GetSpendingSummaryTool::make();
    $tool->handle();
});
