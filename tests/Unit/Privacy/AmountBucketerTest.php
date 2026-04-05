<?php

use TheShit\Finance\Privacy\AmountBucketer;

it('buckets small amounts correctly', function () {
    $bucketer = new AmountBucketer;

    expect($bucketer->bucket(5.00))->toBe('under $10')
        ->and($bucketer->bucket(9.99))->toBe('under $10');
});

it('buckets mid-range amounts correctly', function () {
    $bucketer = new AmountBucketer;

    expect($bucketer->bucket(50.00))->toBe('$50–$100')
        ->and($bucketer->bucket(99.99))->toBe('$50–$100')
        ->and($bucketer->bucket(100.00))->toBe('$100–$250');
});

it('handles exact bucket boundaries', function () {
    $bucketer = new AmountBucketer;

    expect($bucketer->bucket(10.00))->toBe('$10–$25')
        ->and($bucketer->bucket(25.00))->toBe('$25–$50')
        ->and($bucketer->bucket(500.00))->toBe('$500–$1,000');
});

it('buckets large amounts correctly', function () {
    $bucketer = new AmountBucketer;

    expect($bucketer->bucket(15000.00))->toBe('over $10,000');
});

it('handles negative amounts (credits) by absolute value', function () {
    $bucketer = new AmountBucketer;

    expect($bucketer->bucket(-75.00))->toBe('$50–$100');
});
