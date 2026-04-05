<?php

use Prism\Prism\Tool;
use TheShit\Finance\Contracts\FinanceDataProvider;
use TheShit\Finance\Plaid\DTOs\Account;
use TheShit\Finance\Tools\GetAccountBalancesTool;

beforeEach(function () {
    $this->mockAccounts = collect([
        new Account('acc_1', 'Checking', 'Premier Checking', 'depository', 'checking', 1500.00, 1600.00, null, 'USD'),
        new Account('acc_2', 'Visa',     'Visa Signature',   'credit',     'credit card', 4000.00, 1000.00, 5000.00, 'USD'),
    ]);

    $this->mockProvider = Mockery::mock(FinanceDataProvider::class);
    app()->instance(FinanceDataProvider::class, $this->mockProvider);
});

it('returns a Tool instance with correct name', function () {
    $this->mockProvider->shouldReceive('accounts')->andReturn($this->mockAccounts);

    $tool = GetAccountBalancesTool::make();

    expect($tool)->toBeInstanceOf(Tool::class)
        ->and($tool->name())->toBe('get_account_balances');
});

it('returns json with rounded balances', function () {
    $this->mockProvider->shouldReceive('accounts')->andReturn($this->mockAccounts);

    $tool   = GetAccountBalancesTool::make();
    $result = $tool->handle();
    $data   = json_decode($result, true);

    expect($data)->toHaveCount(2)
        ->and($data[0]['type'])->toBe('depository')
        ->and($data[0]['subtype'])->toBe('checking')
        ->and($data[0]['current'])->toContain('$') // rounded, formatted
        ->and($data[1]['limit'])->toContain('$5,000');
});

it('handles null available balance', function () {
    $accounts = collect([
        new Account('acc_1', 'Savings', 'Savings', 'depository', 'savings', null, 5000.00, null, 'USD'),
    ]);

    $this->mockProvider->shouldReceive('accounts')->andReturn($accounts);

    $tool   = GetAccountBalancesTool::make();
    $result = $tool->handle();
    $data   = json_decode($result, true);

    expect($data[0]['available'])->toBeNull()
        ->and($data[0]['limit'])->toBeNull();
});
