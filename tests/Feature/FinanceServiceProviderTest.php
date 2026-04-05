<?php

use TheShit\Finance\Contracts\FinanceDataProvider;
use TheShit\Finance\Plaid\PlaidConnector;
use TheShit\Finance\Privacy\PrivacyTransformer;
use TheShit\Finance\Providers\PlaidProvider;

it('binds PlaidConnector as singleton', function () {
    $a = app(PlaidConnector::class);
    $b = app(PlaidConnector::class);

    expect($a)->toBeInstanceOf(PlaidConnector::class)
        ->and($a)->toBe($b);
});

it('binds FinanceDataProvider to PlaidProvider', function () {
    $provider = app(FinanceDataProvider::class);

    expect($provider)->toBeInstanceOf(PlaidProvider::class);
});

it('binds FinanceDataProvider as singleton', function () {
    $a = app(FinanceDataProvider::class);
    $b = app(FinanceDataProvider::class);

    expect($a)->toBe($b);
});

it('binds PrivacyTransformer as singleton', function () {
    $a = app(PrivacyTransformer::class);
    $b = app(PrivacyTransformer::class);

    expect($a)->toBeInstanceOf(PrivacyTransformer::class)
        ->and($a)->toBe($b);
});

it('configures plaid connector with sandbox environment from test config', function () {
    $connector = app(PlaidConnector::class);

    expect($connector->resolveBaseUrl())->toBe('https://sandbox.plaid.com');
});

it('configures privacy transformer with passthrough driver from test config', function () {
    // TestCase sets FINANCE_PRIVACY_DRIVER=passthrough
    // Verify the transformer doesn't attempt Ollama calls
    $transformer = app(PrivacyTransformer::class);
    $payload = $transformer->prepare(collect(), 'test task');

    expect($payload->data)->toBe([]);
});
