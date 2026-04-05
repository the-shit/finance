<?php

use Saloon\Enums\Method;
use TheShit\Finance\Plaid\Requests\ExchangePublicToken;

it('uses POST method', function () {
    $request = new ExchangePublicToken('public-token-sandbox-abc');
    expect($request->getMethod())->toBe(Method::POST);
});

it('resolves correct endpoint', function () {
    $request = new ExchangePublicToken('public-token-sandbox-abc');
    expect($request->resolveEndpoint())->toBe('/item/public_token/exchange');
});

it('includes public token in body', function () {
    $request = new ExchangePublicToken('public-token-sandbox-abc');
    $body    = $request->defaultBody();

    expect($body['public_token'])->toBe('public-token-sandbox-abc');
});
