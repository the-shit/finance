<?php

use Saloon\Enums\Method;
use TheShit\Finance\Plaid\Requests\SyncTransactions;

it('uses POST method', function () {
    $request = new SyncTransactions('access-token');
    expect($request->getMethod())->toBe(Method::POST);
});

it('resolves correct endpoint', function () {
    $request = new SyncTransactions('access-token');
    expect($request->resolveEndpoint())->toBe('/transactions/sync');
});

it('includes access token and default count in body', function () {
    $request = new SyncTransactions('access-token');
    $body    = $request->defaultBody();

    expect($body['access_token'])->toBe('access-token')
        ->and($body['count'])->toBe(100)
        ->and($body)->not->toHaveKey('cursor');
});

it('includes cursor when provided', function () {
    $request = new SyncTransactions('access-token', 'cursor_xyz');
    $body    = $request->defaultBody();

    expect($body['cursor'])->toBe('cursor_xyz');
});

it('accepts custom count', function () {
    $request = new SyncTransactions('access-token', null, 250);
    $body    = $request->defaultBody();

    expect($body['count'])->toBe(250);
});
