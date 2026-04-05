<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use TheShit\Finance\Plaid\DTOs\Account;
use TheShit\Finance\Plaid\DTOs\Transaction;
use TheShit\Finance\Plaid\PlaidConnector;
use TheShit\Finance\Plaid\Requests\GetAccounts;
use TheShit\Finance\Plaid\Requests\SyncTransactions;
use TheShit\Finance\Contracts\SyncResult;
use TheShit\Finance\Providers\PlaidProvider;

function makePlaidAccount(string $id = 'acc_1'): array
{
    return [
        'account_id'    => $id,
        'name'          => 'Checking',
        'official_name' => 'Premier Checking',
        'type'          => 'depository',
        'subtype'       => 'checking',
        'balances'      => [
            'available'         => 1000.00,
            'current'           => 1100.00,
            'limit'             => null,
            'iso_currency_code' => 'USD',
        ],
    ];
}

function makePlaidTransaction(string $id = 'txn_1', string $date = '2026-04-01'): array
{
    return [
        'transaction_id'    => $id,
        'account_id'        => 'acc_1',
        'amount'            => 42.50,
        'iso_currency_code' => 'USD',
        'name'              => 'Coffee Shop',
        'date'              => $date,
        'category'          => ['Food and Drink'],
        'payment_channel'   => 'in store',
        'pending'           => false,
    ];
}

it('returns typed account collection', function () {
    $mockClient = new MockClient([
        GetAccounts::class => MockResponse::make([
            'accounts' => [makePlaidAccount('acc_1'), makePlaidAccount('acc_2')],
        ]),
    ]);

    $connector = new PlaidConnector('id', 'secret', 'sandbox');
    $connector->withMockClient($mockClient);

    $provider = new PlaidProvider($connector, 'access-token');
    $accounts = $provider->accounts();

    expect($accounts)->toHaveCount(2)
        ->and($accounts->first())->toBeInstanceOf(Account::class)
        ->and($accounts->first()->accountId)->toBe('acc_1');
});

it('returns sync result with typed collections', function () {
    $mockClient = new MockClient([
        SyncTransactions::class => MockResponse::make([
            'added'       => [makePlaidTransaction('txn_1')],
            'modified'    => [],
            'removed'     => [],
            'next_cursor' => 'cursor_next',
            'has_more'    => false,
        ]),
    ]);

    $connector = new PlaidConnector('id', 'secret', 'sandbox');
    $connector->withMockClient($mockClient);

    $provider = new PlaidProvider($connector, 'access-token');
    $result   = $provider->sync();

    expect($result)->toBeInstanceOf(SyncResult::class)
        ->and($result->added)->toHaveCount(1)
        ->and($result->added->first())->toBeInstanceOf(Transaction::class)
        ->and($result->nextCursor)->toBe('cursor_next')
        ->and($result->hasMore)->toBeFalse();
});

it('passes cursor to sync request', function () {
    $mockClient = new MockClient([
        SyncTransactions::class => MockResponse::make([
            'added'       => [],
            'modified'    => [],
            'removed'     => [],
            'next_cursor' => 'cursor_2',
            'has_more'    => false,
        ]),
    ]);

    $connector = new PlaidConnector('id', 'secret', 'sandbox');
    $connector->withMockClient($mockClient);

    $provider = new PlaidProvider($connector, 'access-token');
    $result   = $provider->sync('cursor_1');

    expect($result->nextCursor)->toBe('cursor_2');
});

it('filters transactions by date range', function () {
    $mockClient = new MockClient([
        SyncTransactions::class => MockResponse::make([
            'added'       => [
                makePlaidTransaction('txn_april', '2026-04-05'),
                makePlaidTransaction('txn_march', '2026-03-05'),
            ],
            'modified'    => [],
            'removed'     => [],
            'next_cursor' => 'done',
            'has_more'    => false,
        ]),
    ]);

    $connector = new PlaidConnector('id', 'secret', 'sandbox');
    $connector->withMockClient($mockClient);

    $provider      = new PlaidProvider($connector, 'access-token');
    $transactions  = $provider->transactions(
        Carbon\Carbon::parse('2026-04-01'),
        Carbon\Carbon::parse('2026-04-30'),
    );

    expect($transactions)->toHaveCount(1)
        ->and($transactions->first()->transactionId)->toBe('txn_april');
});

it('paginates through all pages when has_more is true', function () {
    $mockClient = new MockClient([
        SyncTransactions::class => MockResponse::makeSequence([
            MockResponse::make([
                'added'       => [makePlaidTransaction('txn_page1', '2026-04-01')],
                'modified'    => [],
                'removed'     => [],
                'next_cursor' => 'cursor_page2',
                'has_more'    => true,
            ]),
            MockResponse::make([
                'added'       => [makePlaidTransaction('txn_page2', '2026-04-02')],
                'modified'    => [],
                'removed'     => [],
                'next_cursor' => 'cursor_done',
                'has_more'    => false,
            ]),
        ]),
    ]);

    $connector = new PlaidConnector('id', 'secret', 'sandbox');
    $connector->withMockClient($mockClient);

    $provider     = new PlaidProvider($connector, 'access-token');
    $transactions = $provider->transactions(
        Carbon\Carbon::parse('2026-04-01'),
        Carbon\Carbon::parse('2026-04-30'),
    );

    expect($transactions)->toHaveCount(2);
});
