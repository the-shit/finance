<?php

use TheShit\Finance\Plaid\DTOs\Account;

it('maps all fields from plaid response', function () {
    $account = Account::fromPlaid([
        'account_id'    => 'acc_123',
        'name'          => 'Checking',
        'official_name' => 'Premier Checking Account',
        'type'          => 'depository',
        'subtype'       => 'checking',
        'balances'      => [
            'available'         => 1500.00,
            'current'           => 1600.00,
            'limit'             => null,
            'iso_currency_code' => 'USD',
        ],
    ]);

    expect($account->accountId)->toBe('acc_123')
        ->and($account->name)->toBe('Checking')
        ->and($account->officialName)->toBe('Premier Checking Account')
        ->and($account->type)->toBe('depository')
        ->and($account->subtype)->toBe('checking')
        ->and($account->available)->toBe(1500.00)
        ->and($account->current)->toBe(1600.00)
        ->and($account->limit)->toBeNull()
        ->and($account->currencyCode)->toBe('USD');
});

it('falls back to name when official_name is missing', function () {
    $account = Account::fromPlaid([
        'account_id' => 'acc_456',
        'name'       => 'Savings',
        'type'       => 'depository',
        'subtype'    => 'savings',
        'balances'   => [
            'available'         => null,
            'current'           => 5000.00,
            'limit'             => null,
            'iso_currency_code' => 'USD',
        ],
    ]);

    expect($account->officialName)->toBe('Savings')
        ->and($account->available)->toBeNull();
});

it('maps credit account with limit', function () {
    $account = Account::fromPlaid([
        'account_id'    => 'acc_cc',
        'name'          => 'Visa',
        'official_name' => 'Visa Signature',
        'type'          => 'credit',
        'subtype'       => 'credit card',
        'balances'      => [
            'available'         => 4000.00,
            'current'           => 1000.00,
            'limit'             => 5000.00,
            'iso_currency_code' => 'USD',
        ],
    ]);

    expect($account->limit)->toBe(5000.00)
        ->and($account->type)->toBe('credit');
});
