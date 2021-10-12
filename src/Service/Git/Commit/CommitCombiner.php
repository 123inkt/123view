<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Commit;

use DR\GitCommitNotification\Entity\Git\Commit;
use InvalidArgumentException;

class CommitCombiner
{
    /**
     * @param Commit[] $commits
     */
    public function combine(array $commits): Commit
    {
        if (count($commits) === 0) {
            throw new InvalidArgumentException('Array of commits should be atleast size 1');
        }

        if (count($commits) === 1) {
            return reset($commits);
        }

        // take first element of the commits
        $target = array_shift($commits);
        foreach ($commits as $commit) {
            $target = $this->merge($target, $commit);
        }

        return $target;
    }

    /**
     * Merge source commit into the target
     */
    private function merge(Commit $target, Commit $source): Commit
    {
        $target->commitHashes = array_merge($target->commitHashes, $source->commitHashes);
        $target->files        = array_merge($target->files, $source->files);

        return $target;
    }
}
