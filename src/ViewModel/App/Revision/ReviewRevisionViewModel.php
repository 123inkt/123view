<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Revision;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\RevisionFileChange;
use Symfony\Component\Form\FormView;

readonly class ReviewRevisionViewModel
{
    /**
     * @param Revision[]                     $revisions
     * @param array<int, RevisionFileChange> $fileChanges
     */
    public function __construct(
        public array $revisions,
        public array $fileChanges,
        public ?FormView $detachRevisionForm,
        public ?FormView $revisionVisibilityForm
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
