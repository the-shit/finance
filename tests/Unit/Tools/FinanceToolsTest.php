<?php

use Prism\Prism\Tool;
use TheShit\Finance\Contracts\FinanceDataProvider;
use TheShit\Finance\Privacy\PrivacyTransformer;
use TheShit\Finance\Tools\FinanceTools;

beforeEach(function () {
    $this->mockProvider    = Mockery::mock(FinanceDataProvider::class);
    $this->mockTransformer = Mockery::mock(PrivacyTransformer::class);

    app()->instance(FinanceDataProvider::class, $this->mockProvider);
    app()->instance(PrivacyTransformer::class, $this->mockTransformer);
});

it('all() returns three Tool instances', function () {
    $tools = FinanceTools::all();

    expect($tools)->toHaveCount(3)
        ->and($tools[0])->toBeInstanceOf(Tool::class)
        ->and($tools[1])->toBeInstanceOf(Tool::class)
        ->and($tools[2])->toBeInstanceOf(Tool::class);
});

it('spending() returns two Tool instances', function () {
    $tools = FinanceTools::spending();

    expect($tools)->toHaveCount(2)
        ->and($tools[0])->toBeInstanceOf(Tool::class)
        ->and($tools[1])->toBeInstanceOf(Tool::class);
});
