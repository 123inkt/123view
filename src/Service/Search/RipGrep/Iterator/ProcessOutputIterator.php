<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep\Iterator;

use DR\Utils\Assert;
use IteratorAggregate;
use Throwable;
use Traversable;

/**
 * @implements IteratorAggregate<int, string>
 */
class ProcessOutputIterator implements IteratorAggregate
{
    /**
     * @param resource|null $handle
     */
    public function __construct(private $handle)
    {
    }

    public function __destruct()
    {
        if ($this->handle !== null) {
            try {
                pclose($this->handle);
            } catch (Throwable) {
                // Ignore errors on destruct
            }
            $this->handle = null;
        }
    }

    public function getIterator(): Traversable
    {
        $handle = Assert::notNull($this->handle);

        while (feof($handle) === false) {
            $line = fgets($handle);
            if ($line === false) {
                break;
            }
            yield $line;
        }

        pclose($handle);
        $this->handle = null;
    }
}
