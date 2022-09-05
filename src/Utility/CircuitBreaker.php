<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Utility;

use RuntimeException;
use Throwable;

class CircuitBreaker
{
    /**
     * @param int $attempts the amount of attempts to retry the action
     * @param int $wait     the amount of milliseconds to wait between attempts
     */
    public function __construct(private int $attempts, private int $wait)
    {
    }

    /**
     * @template T
     *
     * @param callable(): T $action
     *
     * @return T
     * @throws Throwable
     */
    public function execute(callable $action): mixed
    {
        $exception = null;
        for ($i = 0; $i < $this->attempts; $i++) {
            try {
                return $action();
            } catch (Throwable $e) {
                usleep($this->wait);
                $exception = $e;
            }
        }

        throw new RuntimeException(sprintf('CircuitBreaker failed after %d attempts.', $this->attempts), 0, $exception);
    }
}
