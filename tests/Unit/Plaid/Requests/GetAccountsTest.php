<?php

use Saloon\Enums\Method;
use TheShit\Finance\Plaid\Requests\GetAccounts;

it('uses POST method', function () {
    $request = new GetAccounts('access-sandbox-token');
    expect($request->getMethod())->toBe(Method::POST);
});

it('resolves correct endpoint', function () {
    $request = new GetAccounts('access-sandbox-token');
    expect($request->resolveEndpoint())->toBe('/accounts/get');
});

it('includes access token in body', function () {
    $request = new GetAccounts('access-sandbox-token');
    $body    = $request->defaultBody();

    expect($body['access_token'])->toBe('access-sandbox-token');
});
