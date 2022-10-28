<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Review\Revision;
use Symfony\Component\Form\FormView;

class ReviewRevisionViewModel
{
    /**
     * @param Revision[] $revisions
     */
    public function __construct(private array $revisions, private FormView $detachRevisionForm)
    {
    }

    /**
     * @return Revision[]
     */
    public function getRevisions(): array
    {
        return $this->revisions;
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

    public function getDetachRevisionForm(): ?FormView
    {
        return $this->detachRevisionForm;
    }
}
