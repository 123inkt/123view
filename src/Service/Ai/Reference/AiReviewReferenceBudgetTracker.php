<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Reference;

/**
 * Tracks the cumulative reference-section character budget spent per file-review session, so a
 * single file review can never exhaust the model's context window through repeated reference reads.
 *
 * Sessions are scoped by the orchestrator to a single (code review, file) pair and must be started
 * before, and ended after, the corresponding agent call.
 */
class AiReviewReferenceBudgetTracker
{
    public const int DEFAULT_BUDGET = 60_000;

    /** @var array<string, int> */
    private array $remaining = [];

    public function startSession(string $sessionKey, int $budget = self::DEFAULT_BUDGET): void
    {
        $this->remaining[$sessionKey] = $budget;
    }

    public function endSession(string $sessionKey): void
    {
        unset($this->remaining[$sessionKey]);
    }

    public function remaining(string $sessionKey): int
    {
        return $this->remaining[$sessionKey] ?? 0;
    }

    public function consume(string $sessionKey, int $characters): void
    {
        $this->remaining[$sessionKey] = max(0, $this->remaining($sessionKey) - $characters);
    }
}
