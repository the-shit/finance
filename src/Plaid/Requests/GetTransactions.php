<?php

namespace TheShit\Finance\Plaid\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Date-range transaction fetch (Plaid /transactions/get).
 * Prefer this for expert spend windows; use SyncTransactions for incremental store updates.
 */
class GetTransactions extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $accessToken,
        private readonly string $startDate,
        private readonly string $endDate,
        private readonly int $count = 500,
        private readonly int $offset = 0,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/transactions/get';
    }

    protected function defaultBody(): array
    {
        return [
            'access_token' => $this->accessToken,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'options' => [
                'count' => $this->count,
                'offset' => $this->offset,
            ],
        ];
    }
}
