<?php

namespace TheShit\Finance\Plaid\DTOs;

use Carbon\Carbon;

final class Transaction
{
    public function __construct(
        public readonly string  $transactionId,
        public readonly string  $accountId,
        public readonly float   $amount,       // positive = debit, negative = credit
        public readonly string  $currencyCode,
        public readonly string  $name,         // merchant name or description
        public readonly ?string $merchantName,
        public readonly Carbon  $date,
        public readonly ?Carbon $authorizedAt,
        public readonly array   $category,     // Plaid category hierarchy
        public readonly ?string $categoryId,
        public readonly string  $paymentChannel, // online, in store, other
        public readonly bool    $pending,
        public readonly ?string $logoUrl,
        public readonly ?string $website,
    ) {}

    public function isDebit(): bool
    {
        return $this->amount > 0;
    }

    public function isCredit(): bool
    {
        return $this->amount < 0;
    }

    public static function fromPlaid(array $data): self
    {
        $category = $data['category'] ?? null;
        if (! is_array($category) || $category === []) {
            $primary = $data['personal_finance_category']['primary'] ?? null;
            $category = is_string($primary) && $primary !== '' ? [$primary] : [];
        }

        return new self(
            transactionId: $data['transaction_id'],
            accountId: $data['account_id'],
            amount: (float) $data['amount'],
            currencyCode: $data['iso_currency_code'] ?? 'USD',
            name: $data['name'] ?? ($data['merchant_name'] ?? 'unknown'),
            merchantName: $data['merchant_name'] ?? null,
            date: Carbon::parse($data['date']),
            authorizedAt: isset($data['authorized_datetime'])
                ? Carbon::parse($data['authorized_datetime'])
                : null,
            category: $category,
            categoryId: $data['category_id'] ?? null,
            paymentChannel: $data['payment_channel'] ?? 'other',
            pending: (bool) ($data['pending'] ?? false),
            logoUrl: $data['logo_url'] ?? null,
            website: $data['website'] ?? null,
        );
    }
}
