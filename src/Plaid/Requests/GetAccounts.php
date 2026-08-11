<?php

namespace TheShit\Finance\Plaid\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class GetAccounts extends Request implements HasBody
{
    use HasJsonBody;

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
