<?php

use Carbon\Carbon;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use TheShit\Finance\Plaid\DTOs\Transaction;
use TheShit\Finance\Privacy\AmountBucketer;
use TheShit\Finance\Privacy\CloudPayload;
use TheShit\Finance\Privacy\PrivacyTransformer;

function makeTransaction(array $overrides = []): Transaction
{
    return Transaction::fromPlaid(array_merge([
        'transaction_id'    => 'txn_'.uniqid(),
        'account_id'        => 'acc_123',
        'amount'            => 75.00,
        'iso_currency_code' => 'USD',
        'name'              => 'Whole Foods',
        'merchant_name'     => 'Whole Foods',
        'date'              => '2026-04-01',
        'category'          => ['Food and Drink', 'Groceries'],
        'payment_channel'   => 'in store',
        'pending'           => false,
    ], $overrides));
}

it('returns CloudPayload in passthrough mode', function () {
    $transformer = new PrivacyTransformer(
        bucketer:  new AmountBucketer,
        driver:    'passthrough',
        model:     'llama3.2',
        endpoint:  'http://localhost:11434',
    );

    $transactions = collect([makeTransaction()]);
    $payload = $transformer->prepare($transactions, 'spending summary');

    expect($payload)->toBeInstanceOf(CloudPayload::class)
        ->and($payload->task)->toBe('spending summary')
        ->and($payload->data)->toBeArray()
        ->and($payload->data[0])->toHaveKeys(['amount', 'type', 'category', 'channel', 'month', 'pending']);
});

it('rule-strips exact amounts to buckets', function () {
    $transformer = new PrivacyTransformer(
        bucketer:  new AmountBucketer,
        driver:    'passthrough',
        model:     'llama3.2',
        endpoint:  'http://localhost:11434',
    );

    $transactions = collect([makeTransaction(['amount' => 75.00])]);
    $payload = $transformer->prepare($transactions, 'test');

    expect($payload->data[0]['amount'])->toBe('$50–$100');
});

it('labels debits and credits correctly', function () {
    $transformer = new PrivacyTransformer(
        bucketer:  new AmountBucketer,
        driver:    'passthrough',
        model:     'llama3.2',
        endpoint:  'http://localhost:11434',
    );

    $transactions = collect([
        makeTransaction(['amount' =>  50.00]),
        makeTransaction(['amount' => -1500.00]),
    ]);

    $payload = $transformer->prepare($transactions, 'test');

    expect($payload->data[0]['type'])->toBe('debit')
        ->and($payload->data[1]['type'])->toBe('credit');
});

it('drops day from date, keeps year-month', function () {
    $transformer = new PrivacyTransformer(
        bucketer:  new AmountBucketer,
        driver:    'passthrough',
        model:     'llama3.2',
        endpoint:  'http://localhost:11434',
    );

    $transactions = collect([makeTransaction(['date' => '2026-04-15'])]);
    $payload = $transformer->prepare($transactions, 'test');

    expect($payload->data[0]['month'])->toBe('2026-04');
});

it('falls back to Uncategorized when category is empty', function () {
    $transformer = new PrivacyTransformer(
        bucketer:  new AmountBucketer,
        driver:    'passthrough',
        model:     'llama3.2',
        endpoint:  'http://localhost:11434',
    );

    $transactions = collect([makeTransaction(['category' => []])]);
    $payload = $transformer->prepare($transactions, 'test');

    expect($payload->data[0]['category'])->toBe('Uncategorized');
});

it('uses most specific plaid category level', function () {
    $transformer = new PrivacyTransformer(
        bucketer:  new AmountBucketer,
        driver:    'passthrough',
        model:     'llama3.2',
        endpoint:  'http://localhost:11434',
    );

    $transactions = collect([makeTransaction(['category' => ['Food and Drink', 'Restaurants', 'Fast Food']])]);
    $payload = $transformer->prepare($transactions, 'test');

    expect($payload->data[0]['category'])->toBe('Fast Food');
});

it('calls ollama and returns reduced payload', function () {
    $fake = Prism::fake([
        TextResponseFake::make()->withText('{"Groceries":{"count":3,"range":"$50-$100"}}'),
    ]);

    $transformer = new PrivacyTransformer(
        bucketer:  new AmountBucketer,
        driver:    'ollama',
        model:     'llama3.2',
        endpoint:  'http://localhost:11434',
    );

    $transactions = collect([makeTransaction()]);
    $payload = $transformer->prepare($transactions, 'spending by category');

    expect($payload)->toBeInstanceOf(CloudPayload::class)
        ->and($payload->data)->toHaveKey('Groceries');
});

it('falls back to rule-stripped data when ollama returns invalid json', function () {
    Prism::fake([
        TextResponseFake::make()->withText('this is not json at all'),
    ]);

    $transformer = new PrivacyTransformer(
        bucketer:  new AmountBucketer,
        driver:    'ollama',
        model:     'llama3.2',
        endpoint:  'http://localhost:11434',
    );

    $transactions = collect([makeTransaction()]);
    $payload = $transformer->prepare($transactions, 'spending by category');

    expect($payload->meta)->toHaveKey('fallback')
        ->and($payload->meta['fallback'])->toBeTrue()
        ->and($payload->data)->toBeArray();
});
