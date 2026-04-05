<?php

namespace TheShit\Finance\Plaid\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAccounts extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $accessToken,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/accounts/get';
    }

    protected function defaultBody(): array
    {
        return [
            'access_token' => $this->accessToken,
        ];
    }
}
