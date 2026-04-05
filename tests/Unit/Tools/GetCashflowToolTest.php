<?php

use Carbon\Carbon;
use Prism\Prism\Tool;
use TheShit\Finance\Contracts\FinanceDataProvider;
use TheShit\Finance\Privacy\CloudPayload;
use TheShit\Finance\Privacy\PrivacyTransformer;
use TheShit\Finance\Tools\GetCashflowTool;

beforeEach(function () {
    Carbon::setTestNow('2026-04-15 12:00:00');

    $this->mockProvider    = Mockery::mock(FinanceDataProvider::class);
    $this->mockTransformer = Mockery::mock(PrivacyTransformer::class);

    app()->instance(FinanceDataProvider::class, $this->mockProvider);
    app()->instance(PrivacyTransformer::class, $this->mockTransformer);
});

afterEach(fn () => Carbon::setTestNow());

it('returns a Tool instance with correct name', function () {
    $this->mockProvider->shouldReceive('transactions')->andReturn(collect());
    $this->mockTransformer->shouldReceive('prepare')->andReturn(
        new CloudPayload('cashflow', [])
    );

    $tool = GetCashflowTool::make();

    expect($tool)->toBeInstanceOf(Tool::class)
        ->and($tool->name())->toBe('get_cashflow');
});

it('returns json from cloud payload', function () {
    $this->mockProvider->shouldReceive('transactions')->andReturn(collect());
    $this->mockTransformer->shouldReceive('prepare')->andReturn(
        new CloudPayload('cashflow', ['net' => '$500-$1,000'])
    );

    $tool   = GetCashflowTool::make();
    $result = $tool->handle('last_month');

    expect($result)->toBeString()
        ->and(json_decode($result, true))->toHaveKey('data');
});

it('uses last_month period correctly', function () {
    $this->mockProvider
        ->shouldReceive('transactions')
        ->withArgs(fn ($from, $to) =>
            $from->toDateString() === '2026-03-01' &&
            $to->toDateString() === '2026-03-31'
        )
        ->andReturn(collect());

    $this->mockTransformer->shouldReceive('prepare')->andReturn(
        new CloudPayload('cashflow', [])
    );

    $tool = GetCashflowTool::make();
    $tool->handle('last_month');
});

it('filters pending transactions', function () {
    $transactions = collect([
        \TheShit\Finance\Plaid\DTOs\Transaction::fromPlaid([
            'transaction_id'    => 'txn_pending',
            'account_id'        => 'acc_1',
            'amount'            => 100.00,
            'iso_currency_code' => 'USD',
            'name'              => 'Pending',
            'date'              => '2026-04-10',
            'payment_channel'   => 'online',
            'pending'           => true,
        ]),
    ]);

    $this->mockProvider->shouldReceive('transactions')->andReturn($transactions);
    $this->mockTransformer
        ->shouldReceive('prepare')
        ->withArgs(fn ($txns) => $txns->isEmpty())
        ->andReturn(new CloudPayload('cashflow', []));

    $tool = GetCashflowTool::make();
    $tool->handle('current_month');
});
