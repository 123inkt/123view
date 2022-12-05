<?php
declare(strict_types=1);

namespace DR\Review\Utility;

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
            if ($i > 0) {
                usleep($this->wait);
            }

            try {
                return $action();
            } catch (Throwable $e) {
                $exception = $e;
            }
        }

        throw $exception ?? new RuntimeException('Failed after ' . $this->attempts . ' attempts');
    }
}
