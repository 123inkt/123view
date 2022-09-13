<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Utility;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Utility\CircuitBreaker;
use RuntimeException;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Utility\CircuitBreaker
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

        $result = $circuitBreaker->execute(static function () use (&$attempt) {
            if (++$attempt === 1) {
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
            $circuitBreaker->execute(static function () use (&$attempt) {
                ++$attempt;
                throw new RuntimeException('Failed');
            });
            $success = true;
        } catch (Throwable) {
            $success = false;
        }
        static::assertFalse($success);
        static::assertSame(2, $attempt);
    }
}
