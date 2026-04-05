<?php

namespace TheShit\Finance\Privacy;

/**
 * The minimal, task-specific, PII-free payload sent to a cloud AI provider.
 *
 * Shape varies by task — cloud only sees what it needs.
 */
final class CloudPayload
{
    public function __construct(
        public readonly string $task,
        public readonly array  $data,
        public readonly array  $meta = [],
    ) {}

    public function toArray(): array
    {
        return [
            'task' => $this->task,
            'data' => $this->data,
            'meta' => $this->meta,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
