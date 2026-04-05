<?php

namespace TheShit\Finance\Plaid\DTOs;

final class Account
{
    public function __construct(
        public readonly string $accountId,
        public readonly string $name,
        public readonly string $officialName,
        public readonly string $type,        // depository, credit, loan, investment
        public readonly string $subtype,     // checking, savings, credit card, etc.
        public readonly ?float $available,
        public readonly float  $current,
        public readonly ?float $limit,
        public readonly string $currencyCode,
    ) {}

    public static function fromPlaid(array $data): self
    {
        return new self(
            accountId:    $data['account_id'],
            name:         $data['name'],
            officialName: $data['official_name'] ?? $data['name'],
            type:         $data['type'],
            subtype:      $data['subtype'],
            available:    $data['balances']['available'] ?? null,
            current:      $data['balances']['current'],
            limit:        $data['balances']['limit'] ?? null,
            currencyCode: $data['balances']['iso_currency_code'] ?? 'USD',
        );
    }
}
