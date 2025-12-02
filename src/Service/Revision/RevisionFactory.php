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
            $revisions[] = $revision = new Revision();
            $revision->setRepository($commit->repository);
            $revision->setAuthorEmail($commit->author->email);
            $revision->setAuthorName($commit->author->name);
            $revision->setCreateTimestamp($commit->date->getTimestamp());
            $revision->setCommitHash($hash);
            $revision->setParentHash($commit->parentHash);
            $revision->setTitle(mb_substr(trim($commit->subject), 0, 255));
            $revision->setDescription(mb_substr($commit->body, 0, 255));

            $remoteRef = $commit->getRemoteRef();

            if ($remoteRef !== null && $remoteRef !== $hash && preg_match('/^[a-f0-9]{8,11}$/', $remoteRef) !== 1) {
                $revision->setFirstBranch(mb_substr($remoteRef, 0, 255));
            } else {
                $revision->setFirstBranch(null);
            }
        }

        return $revisions;
    }
}
