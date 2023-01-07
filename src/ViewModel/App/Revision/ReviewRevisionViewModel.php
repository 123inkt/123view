<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Revision;

use DR\Review\Entity\Revision\Revision;
use Symfony\Component\Form\FormView;

class ReviewRevisionViewModel
{
    /**
     * @param Revision[] $revisions
     */
    public function __construct(
        public readonly array $revisions,
        public readonly FormView $detachRevisionForm,
        public readonly FormView $revisionVisibilityForm
    ) {
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
