<?php

use Saloon\Enums\Method;
use TheShit\Finance\Plaid\Requests\CreateLinkToken;

it('uses POST method', function () {
    $request = new CreateLinkToken('user_123');
    expect($request->getMethod())->toBe(Method::POST);
});

it('resolves correct endpoint', function () {
    $request = new CreateLinkToken('user_123');
    expect($request->resolveEndpoint())->toBe('/link/token/create');
});

it('includes required body fields', function () {
    $request = new CreateLinkToken('user_123');
    $body    = $request->defaultBody();

    expect($body['user'])->toBe(['client_user_id' => 'user_123'])
        ->and($body['client_name'])->toBe('Finance')
        ->and($body['products'])->toBe(['transactions'])
        ->and($body['country_codes'])->toBe(['US'])
        ->and($body['language'])->toBe('en');
});

it('includes webhook when provided', function () {
    $request = new CreateLinkToken('user_123', webhook: 'https://example.com/webhook');
    $body    = $request->defaultBody();

    expect($body['webhook'])->toBe('https://example.com/webhook');
});

it('excludes webhook when null', function () {
    $request = new CreateLinkToken('user_123');
    $body    = $request->defaultBody();

    expect($body)->not->toHaveKey('webhook');
});

it('accepts custom products and country codes', function () {
    $request = new CreateLinkToken('user_123', ['auth', 'transactions'], ['CA']);
    $body    = $request->defaultBody();

    expect($body['products'])->toBe(['auth', 'transactions'])
        ->and($body['country_codes'])->toBe(['CA']);
});
