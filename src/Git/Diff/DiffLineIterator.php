<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Git\Diff;

use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<DiffLine>
 */
class DiffLineIterator implements IteratorAggregate
{
    /** @var Commit[] */
    private array $commits;

    /**
     * @param Commit[] $commits
     */
    public function __construct(array $commits)
    {
        $this->commits = $commits;
    }

    /**
     * @return DiffLine[]|Generator
     */
    public function getIterator(): Generator
    {
        foreach ($this->commits as $commit) {
            foreach ($commit->files as $diffFile) {
                foreach ($diffFile->blocks as $diffBlock) {
                    foreach ($diffBlock->lines as $diffLine) {
                        yield $diffLine;
                    }
                }
            }
        }
    }
}
