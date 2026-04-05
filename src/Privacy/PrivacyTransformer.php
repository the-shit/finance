<?php

namespace TheShit\Finance\Privacy;

use EchoLabs\Prism\Facades\Prism;
use Illuminate\Support\Collection;
use TheShit\Finance\Plaid\DTOs\Transaction;

/**
 * Prepares financial data for a cloud AI provider.
 *
 * Two responsibilities, one operation:
 *   1. Privacy  — strip PII before anything leaves the machine
 *   2. Efficiency — reduce to exactly what the task requires
 *
 * A local Ollama model does both simultaneously: given the task,
 * aggregate and strip the data down to the minimal safe payload.
 *
 * Cloud provider receives a tiny, clean, task-specific JSON blob —
 * not raw transactions, not merchant names, not account IDs.
 *
 * Set FINANCE_PRIVACY_DRIVER=passthrough to skip Ollama entirely
 * (rule-based strip only — useful for full-local workflows where
 * the same local model handles analysis too).
 */
class PrivacyTransformer
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        You are a privacy filter and data reducer for personal financial data.

        You will receive:
          - A task description (what the AI needs to accomplish)
          - Raw financial transactions (pre-stripped of IDs and exact amounts)

        Your job is to return the absolute minimum data required for the task.

        Rules:
          - Never include merchant names, business names, or personal names
          - Never include exact dollar amounts — use the provided ranges
          - Never include account identifiers
          - Never include dates more specific than year-month
          - Aggregate where possible (e.g. category totals, not individual transactions)
          - If the task only needs category totals, return only category totals
          - If the task needs transaction count, include count — not individual records
          - Return valid JSON only — no explanation, no markdown

        The goal: smallest payload that lets a cloud AI complete the task accurately.
        PROMPT;

    public function __construct(
        private readonly AmountBucketer $bucketer,
        private readonly string         $driver,   // 'ollama' | 'passthrough'
        private readonly string         $model,
        private readonly string         $endpoint,
    ) {}

    /**
     * Reduce transactions to a minimal, PII-free cloud payload for a given task.
     *
     * @param  Collection<Transaction>  $transactions
     */
    public function prepare(Collection $transactions, string $task): CloudPayload
    {
        $stripped = $this->ruleStrip($transactions);

        if ($this->driver === 'passthrough') {
            return new CloudPayload(task: $task, data: $stripped);
        }

        return $this->ollamaReduce($stripped, $task);
    }

    /**
     * Rule-based strip — fast, deterministic, no model required.
     * Produces an intermediate representation safe to hand to Ollama.
     */
    private function ruleStrip(Collection $transactions): array
    {
        return $transactions->map(fn (Transaction $t) => [
            'amount'   => $this->bucketer->bucket($t->amount),
            'type'     => $t->isDebit() ? 'debit' : 'credit',
            'category' => $this->plaidCategory($t),
            'channel'  => $t->paymentChannel,
            'month'    => $t->date->format('Y-m'),
            'pending'  => $t->pending,
        ])->values()->all();
    }

    /**
     * Ollama pass — task-aware aggregation and final PII sweep.
     * Reduces the stripped array to exactly what the task needs.
     */
    private function ollamaReduce(array $stripped, string $task): CloudPayload
    {
        $prompt = implode("\n\n", [
            "Task: {$task}",
            'Transactions:',
            json_encode($stripped, JSON_PRETTY_PRINT),
        ]);

        $response = Prism::text()
            ->usingOllama($this->model, $this->endpoint)
            ->withSystemPrompt(self::SYSTEM_PROMPT)
            ->withPrompt($prompt)
            ->generate();

        $reduced = json_decode($response->text, true);

        // If Ollama returns bad JSON, fall back to rule-stripped data
        if (! is_array($reduced)) {
            return new CloudPayload(task: $task, data: $stripped, meta: ['fallback' => true]);
        }

        return new CloudPayload(task: $task, data: $reduced);
    }

    private function plaidCategory(Transaction $t): string
    {
        return ! empty($t->category) ? last($t->category) : 'Uncategorized';
    }
}
