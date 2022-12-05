<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Utility;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\CircuitBreaker;
use RuntimeException;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\Utility\CircuitBreaker
 * @covers ::__construct
 */
class CircuitBreakerTest extends AbstractTestCase
{
    /**
     * @covers ::execute
     * @throws Throwable
     */
    public function testExecuteShouldSucceedAfterTwoAttempts(): void
    {
        $attempt        = 0;
        $circuitBreaker = new CircuitBreaker(2, 1);

        $result = $circuitBreaker->execute(static function () use (&$attempt) { // phpcs:ignore
            ++$attempt;
            if ($attempt === 1) {
                throw new RuntimeException('Failed');
            }

            return true;
        });
        static::assertSame(2, $attempt);
        static::assertTrue($result);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteShouldFailAfterTwoAttempts(): void
    {
        $attempt        = 0;
        $circuitBreaker = new CircuitBreaker(2, 1);

        try {
            $circuitBreaker->execute(static function () use (&$attempt): void { // phpcs:ignore
                ++$attempt;
                throw new RuntimeException('Failed');
            });
        } catch (Throwable) {
            // ignored, expecting exception
        }
        static::assertSame(2, $attempt); // @phpstan-ignore-line
    }
}
