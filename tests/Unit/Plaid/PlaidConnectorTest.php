<?php

use TheShit\Finance\Plaid\PlaidConnector;

it('resolves sandbox base url', function () {
    $connector = new PlaidConnector('id', 'secret', 'sandbox');
    expect($connector->resolveBaseUrl())->toBe('https://sandbox.plaid.com');
});

it('resolves development base url', function () {
    $connector = new PlaidConnector('id', 'secret', 'development');
    expect($connector->resolveBaseUrl())->toBe('https://development.plaid.com');
});

it('resolves production base url', function () {
    $connector = new PlaidConnector('id', 'secret', 'production');
    expect($connector->resolveBaseUrl())->toBe('https://production.plaid.com');
});

it('defaults to sandbox for unknown environment', function () {
    $connector = new PlaidConnector('id', 'secret', 'unknown');
    expect($connector->resolveBaseUrl())->toBe('https://sandbox.plaid.com');
});
