<?php

namespace TheShit\Finance\Contracts;

use Illuminate\Support\Collection;

final class SyncResult
{
    public function __construct(
        public readonly Collection $added,
        public readonly Collection $modified,
        public readonly Collection $removed,
        public readonly string $nextCursor,
        public readonly bool $hasMore,
    ) {}
}
