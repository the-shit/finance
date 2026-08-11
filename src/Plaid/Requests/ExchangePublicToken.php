<?php

namespace TheShit\Finance\Plaid\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Step 2: Exchange the public_token from Plaid Link for a permanent access_token.
 * Store the access_token securely — it's how you read this user's data forever.
 */
class ExchangePublicToken extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $publicToken,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/item/public_token/exchange';
    }

    protected function defaultBody(): array
    {
        return [
            'public_token' => $this->publicToken,
        ];
    }
}
