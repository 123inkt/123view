<?php
declare(strict_types=1);

namespace DR\Review\Model\Review;

use DR\Review\Entity\Git\Commit;
use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<Commit>
 */
class UniqueCommitIterator implements IteratorAggregate
{
    /** @var array<string, true> */
    private array $hashes = [];

    /**
     * @phpstan-param array<(callable(bool: &$repeat): Commit[])> $callbacks
     *
     * @param array<callable> $callbacks
     */
    public function __construct(private readonly array $callbacks)
    {
    }

    /**
     * @return Generator<Commit>
     */
    public function getIterator(): Generator
    {
        foreach ($this->callbacks as $callback) {
            do {
                $repeat = false;
                /** @var Commit $commit */
                foreach ($callback($repeat) as $commit) {
                    $hash = $commit->commitHashes[0];
                    if (isset($this->hashes[$hash])) {
                        continue;
                    }

                    $this->hashes[$hash] = true;
                    yield $commit;
                }
            } while ($repeat);
        }
    }
}
