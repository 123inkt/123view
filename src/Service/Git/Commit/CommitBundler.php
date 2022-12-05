<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Commit;

use DR\Review\Entity\Git\Commit;

/**
 * Combine similar commits together, based on author, branch and commit subject line
 */
class CommitBundler
{
    private CommitCombiner $combiner;

    public function __construct(CommitCombiner $combiner)
    {
        $this->combiner = $combiner;
    }

    /**
     * @param Commit[] $commits
     *
     * @return Commit[]
     */
    public function bundle(array $commits): array
    {
        $result = [];
        foreach ($this->getGroupedCommits($commits) as $group) {
            $result[] = $this->combiner->combine($group);
        }

        return $result;
    }

    /**
     * @param Commit[] $commits
     *
     * @return Commit[][]
     */
    private function getGroupedCommits(array $commits): array
    {
        $groups = [];
        foreach ($commits as $commit) {
            // find the parent commit in the group
            foreach ($groups as $index => $group) {
                /** @var Commit $target */
                foreach ($group as $target) {
                    if (in_array($commit->parentHash, $target->commitHashes, true) && $this->equals($commit, $target)) {
                        $groups[$index][] = $commit;
                        continue 3;
                    }
                }
            }

            // append to the end
            $groups[] = [$commit];
        }

        return $groups;
    }

    /**
     * Are 2 commits equal based on repository, author, ref and subject
     */
    private function equals(Commit $commitA, Commit $commitB): bool
    {
        return $commitA->repository === $commitB->repository
            && $commitA->author->email === $commitB->author->email
            && $commitA->getRemoteRef() === $commitB->getRemoteRef()
            && $commitA->getSubjectLine() === $commitB->getSubjectLine();
    }
}
