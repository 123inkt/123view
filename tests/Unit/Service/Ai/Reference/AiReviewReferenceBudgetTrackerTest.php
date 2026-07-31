<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Reference;

use DR\Review\Service\Ai\Reference\AiReviewReferenceBudgetTracker;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AiReviewReferenceBudgetTracker::class)]
class AiReviewReferenceBudgetTrackerTest extends AbstractTestCase
{
    private AiReviewReferenceBudgetTracker $tracker;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracker = new AiReviewReferenceBudgetTracker();
    }

    public function testRemainingShouldBeZeroForUnknownSession(): void
    {
        static::assertSame(0, $this->tracker->remaining('unknown'));
    }

    public function testStartSessionShouldSetInitialBudget(): void
    {
        $this->tracker->startSession('session-1', 100);

        static::assertSame(100, $this->tracker->remaining('session-1'));
    }

    public function testStartSessionShouldUseDefaultBudgetWhenNotSpecified(): void
    {
        $this->tracker->startSession('session-1');

        static::assertSame(AiReviewReferenceBudgetTracker::DEFAULT_BUDGET, $this->tracker->remaining('session-1'));
    }

    public function testConsumeShouldReduceRemainingBudget(): void
    {
        $this->tracker->startSession('session-1', 100);
        $this->tracker->consume('session-1', 40);

        static::assertSame(60, $this->tracker->remaining('session-1'));
    }

    public function testConsumeShouldNeverGoBelowZero(): void
    {
        $this->tracker->startSession('session-1', 100);
        $this->tracker->consume('session-1', 150);

        static::assertSame(0, $this->tracker->remaining('session-1'));
    }

    public function testEndSessionShouldResetRemainingBudget(): void
    {
        $this->tracker->startSession('session-1', 100);
        $this->tracker->endSession('session-1');

        static::assertSame(0, $this->tracker->remaining('session-1'));
    }

    public function testSessionsAreIndependent(): void
    {
        $this->tracker->startSession('session-1', 100);
        $this->tracker->startSession('session-2', 50);
        $this->tracker->consume('session-1', 10);

        static::assertSame(90, $this->tracker->remaining('session-1'));
        static::assertSame(50, $this->tracker->remaining('session-2'));
    }
}
