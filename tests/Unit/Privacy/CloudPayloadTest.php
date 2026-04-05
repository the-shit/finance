<?php

use TheShit\Finance\Privacy\CloudPayload;

it('stores task and data', function () {
    $payload = new CloudPayload(
        task: 'spending summary',
        data: ['Groceries' => ['count' => 5]],
    );

    expect($payload->task)->toBe('spending summary')
        ->and($payload->data)->toBe(['Groceries' => ['count' => 5]])
        ->and($payload->meta)->toBe([]);
});

it('stores optional meta', function () {
    $payload = new CloudPayload(
        task: 'cashflow',
        data: [],
        meta: ['fallback' => true],
    );

    expect($payload->meta)->toBe(['fallback' => true]);
});

it('converts to array', function () {
    $payload = new CloudPayload(
        task: 'spending summary',
        data: ['key' => 'value'],
        meta: ['source' => 'ollama'],
    );

    expect($payload->toArray())->toBe([
        'task' => 'spending summary',
        'data' => ['key' => 'value'],
        'meta' => ['source' => 'ollama'],
    ]);
});

it('converts to json', function () {
    $payload = new CloudPayload(
        task: 'balances',
        data: ['checking' => '$1,000'],
    );

    $json = $payload->toJson();
    $decoded = json_decode($json, true);

    expect($decoded['task'])->toBe('balances')
        ->and($decoded['data']['checking'])->toBe('$1,000');
});
