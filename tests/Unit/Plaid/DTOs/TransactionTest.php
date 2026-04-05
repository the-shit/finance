<?php

use Carbon\Carbon;
use TheShit\Finance\Plaid\DTOs\Transaction;

it('maps all fields from plaid response', function () {
    $transaction = Transaction::fromPlaid([
        'transaction_id'   => 'txn_abc',
        'account_id'       => 'acc_123',
        'amount'           => 42.50,
        'iso_currency_code' => 'USD',
        'name'             => 'Whole Foods Market',
        'merchant_name'    => 'Whole Foods',
        'date'             => '2026-04-01',
        'authorized_datetime' => '2026-04-01T10:00:00Z',
        'category'         => ['Food and Drink', 'Groceries'],
        'category_id'      => 'cat_001',
        'payment_channel'  => 'in store',
        'pending'          => false,
        'logo_url'         => 'https://logo.example.com',
        'website'          => 'wholefoodsmarket.com',
    ]);

    expect($transaction->transactionId)->toBe('txn_abc')
        ->and($transaction->accountId)->toBe('acc_123')
        ->and($transaction->amount)->toBe(42.50)
        ->and($transaction->currencyCode)->toBe('USD')
        ->and($transaction->name)->toBe('Whole Foods Market')
        ->and($transaction->merchantName)->toBe('Whole Foods')
        ->and($transaction->date)->toBeInstanceOf(Carbon::class)
        ->and($transaction->date->toDateString())->toBe('2026-04-01')
        ->and($transaction->authorizedAt)->toBeInstanceOf(Carbon::class)
        ->and($transaction->category)->toBe(['Food and Drink', 'Groceries'])
        ->and($transaction->categoryId)->toBe('cat_001')
        ->and($transaction->paymentChannel)->toBe('in store')
        ->and($transaction->pending)->toBeFalse()
        ->and($transaction->logoUrl)->toBe('https://logo.example.com')
        ->and($transaction->website)->toBe('wholefoodsmarket.com');
});

it('handles missing optional fields', function () {
    $transaction = Transaction::fromPlaid([
        'transaction_id'    => 'txn_min',
        'account_id'        => 'acc_123',
        'amount'            => 10.00,
        'iso_currency_code' => 'USD',
        'name'              => 'ATM Withdrawal',
        'date'              => '2026-04-01',
        'pending'           => false,
    ]);

    expect($transaction->merchantName)->toBeNull()
        ->and($transaction->authorizedAt)->toBeNull()
        ->and($transaction->category)->toBe([])
        ->and($transaction->categoryId)->toBeNull()
        ->and($transaction->paymentChannel)->toBe('other')
        ->and($transaction->logoUrl)->toBeNull()
        ->and($transaction->website)->toBeNull();
});

it('identifies debits correctly', function () {
    $transaction = Transaction::fromPlaid([
        'transaction_id'    => 'txn_debit',
        'account_id'        => 'acc_123',
        'amount'            => 50.00,
        'iso_currency_code' => 'USD',
        'name'              => 'Coffee Shop',
        'date'              => '2026-04-01',
        'pending'           => false,
    ]);

    expect($transaction->isDebit())->toBeTrue()
        ->and($transaction->isCredit())->toBeFalse();
});

it('identifies credits correctly', function () {
    $transaction = Transaction::fromPlaid([
        'transaction_id'    => 'txn_credit',
        'account_id'        => 'acc_123',
        'amount'            => -1500.00,
        'iso_currency_code' => 'USD',
        'name'              => 'Direct Deposit',
        'date'              => '2026-04-01',
        'pending'           => false,
    ]);

    expect($transaction->isCredit())->toBeTrue()
        ->and($transaction->isDebit())->toBeFalse();
});
