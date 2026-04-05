<?php

namespace TheShit\Finance\Plaid\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Plaid's modern transaction sync endpoint.
 * Cursor-based — store nextCursor after each call and pass it next time.
 * First call: omit cursor to get full history.
 */
class SyncTransactions extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly string  $accessToken,
        private readonly ?string $cursor = null,
        private readonly int     $count = 100,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/transactions/sync';
    }

    protected function defaultBody(): array
    {
        return array_filter([
            'access_token' => $this->accessToken,
            'cursor'       => $this->cursor,
            'count'        => $this->count,
        ], fn ($v) => $v !== null);
    }
}
