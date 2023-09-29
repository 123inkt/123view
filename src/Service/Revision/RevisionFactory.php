<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Revision\Revision;

class RevisionFactory
{
    /**
     * @return Revision[]
     */
    public function createFromCommit(Commit $commit): array
    {
        $revisions = [];

        foreach ($commit->commitHashes as $hash) {
            $remoteRef = $commit->getRemoteRef();

            $revisions[] = $revision = new Revision();
            $revision->setRepository($commit->repository);
            $revision->setAuthorEmail($commit->author->email);
            $revision->setAuthorName($commit->author->name);
            $revision->setCreateTimestamp($commit->date->getTimestamp());
            $revision->setCommitHash($hash);
            $revision->setTitle(mb_substr(trim($commit->subject), 0, 255));
            $revision->setDescription(mb_substr($commit->body, 0, 255));
            $revision->setFirstBranch($remoteRef === null ? null : mb_substr($remoteRef, 0, 255));
        }

        return $revisions;
    }
}
