<?php

namespace TheShit\Finance\Plaid;

use Saloon\Contracts\Body\HasBody;
use Saloon\Http\Connector;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class PlaidConnector extends Connector implements HasBody
{
    use AlwaysThrowOnErrors;
    use HasJsonBody;

    public function __construct(
        private readonly string $clientId,
        private readonly string $secret,
        private readonly string $environment = 'sandbox',
    ) {}

    public function resolveBaseUrl(): string
    {
        return match ($this->environment) {
            'production' => 'https://production.plaid.com',
            'development' => 'https://development.plaid.com',
            default => 'https://sandbox.plaid.com',
        };
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    protected function defaultBody(): array
    {
        return [
            'client_id' => $this->clientId,
            'secret' => $this->secret,
        ];
    }
}
