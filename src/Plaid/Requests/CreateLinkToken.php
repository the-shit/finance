<?php

namespace TheShit\Finance\Plaid\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Step 1: Create a link token to initialize Plaid Link on the frontend.
 * Exchange the resulting public_token via ExchangePublicToken.
 */
class CreateLinkToken extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $userId,
        private readonly array $products = ['transactions'],
        private readonly array $countryCodes = ['US'],
        private readonly ?string $webhook = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/link/token/create';
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'user' => ['client_user_id' => $this->userId],
            'client_name' => 'Finance',
            'products' => $this->products,
            'country_codes' => $this->countryCodes,
            'language' => 'en',
            'webhook' => $this->webhook,
        ]);
    }
}
