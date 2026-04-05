<?php

namespace TheShit\Finance\Privacy;

/**
 * Converts exact dollar amounts into ranges safe for cloud AI.
 * Buckets are intentionally wide enough to be non-identifying.
 */
final class AmountBucketer
{
    private const BUCKETS = [
        10    => 'under $10',
        25    => '$10–$25',
        50    => '$25–$50',
        100   => '$50–$100',
        250   => '$100–$250',
        500   => '$250–$500',
        1000  => '$500–$1,000',
        2500  => '$1,000–$2,500',
        5000  => '$2,500–$5,000',
        10000 => '$5,000–$10,000',
    ];

    public function bucket(float $amount): string
    {
        $abs = abs($amount);

        foreach (self::BUCKETS as $ceiling => $label) {
            if ($abs < $ceiling) {
                return $label;
            }
        }

        return 'over $10,000';
    }
}
