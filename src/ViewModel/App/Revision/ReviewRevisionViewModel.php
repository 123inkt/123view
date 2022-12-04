<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Revision;

use DR\GitCommitNotification\Entity\Review\Revision;
use Symfony\Component\Form\FormView;

class ReviewRevisionViewModel
{
    /**
     * @param Revision[] $revisions
     */
    public function __construct(public readonly array $revisions, public readonly FormView $detachRevisionForm)
    {
    }

    public function getRevision(string $revisionId): ?Revision
    {
        foreach ($this->revisions as $revision) {
            if ($revision->getId() === (int)$revisionId) {
                return $revision;
            }
        }

        return null;
    }
}
