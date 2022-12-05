<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Review\Revision;

class RevisionFactory
{
    /**
     * @param Commit[] $commits
     *
     * @return Revision[]
     */
    public function createFromCommits(array $commits): array
    {
        $revisions = [];
        foreach ($commits as $commit) {
            $revisions[] = $this->createFromCommit($commit);
        }

        return array_merge(...$revisions);
    }

    /**
     * @return Revision[]
     */
    public function createFromCommit(Commit $commit): array
    {
        $revisions = [];

        foreach ($commit->commitHashes as $hash) {
            $revisions[] = $revision = new Revision();
            $revision->setRepository($commit->repository);
            $revision->setAuthorEmail($commit->author->email);
            $revision->setAuthorName($commit->author->name);
            $revision->setCreateTimestamp($commit->date->getTimestamp());
            $revision->setCommitHash($hash);
            $revision->setTitle(mb_substr(trim($commit->getSubjectLine()), 0, 255));
            $revision->setDescription(mb_substr($commit->getCommitMessage(), 0, 255));
        }

        return $revisions;
    }
}
