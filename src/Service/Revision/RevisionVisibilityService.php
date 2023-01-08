<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionVisibility;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;

class RevisionVisibilityService
{
    public function __construct(private readonly ?User $user, readonly RevisionVisibilityRepository $visibilityRepository)
    {
    }

    /**
     * @param Revision[] $revisions
     *
     * @return Revision[]
     */
    public function getVisibleRevisions(CodeReview $review, iterable $revisions): array
    {
        $visibilities = $this->visibilityRepository->findBy(['review' => $review->getId(), 'user' => $this->user->getId()]);

        $result = [];
        foreach ($revisions as $revision) {
            foreach ($visibilities as $visibility) {
                if ($revision->getId() !== $visibility->getRevision()?->getId()) {
                    continue;
                }
                if ($visibility->isVisible()) {
                    $result[] = $revision;
                }
                continue 2;
            }

            $result[] = $revision;
        }

        return $result;
    }

    /**
     * @param Revision[] $revisions
     *
     * @return RevisionVisibility[]
     */
    public function getRevisionVisibilities(CodeReview $review, iterable $revisions, User $user): array
    {
        $visibilities = $this->visibilityRepository->findBy(['review' => $review->getId(), 'user' => $user->getId()]);

        $result = [];
        foreach ($revisions as $revision) {
            foreach ($visibilities as $visibility) {
                if ($revision->getId() === $visibility->getRevision()?->getId()) {
                    $result[] = $visibility;
                    continue 2;
                }
            }

            $result[] = (new RevisionVisibility())
                ->setReview($review)
                ->setUser($this->user)
                ->setRevision($revision)
                ->setVisible(true);
        }

        return $result;
    }

    /**
     * @param Revision[] $revisions
     */
    public function setRevisionVisibility(CodeReview $review, iterable $revisions, User $user, bool $visible): void
    {
        if (count($revisions) === 0) {
            return;
        }

        $visibilities = $this->getRevisionVisibilities($review, $revisions, $user);
        foreach ($visibilities as $visibility) {
            $visibility->setVisible($visible);
        }
        $this->visibilityRepository->saveAll($visibilities, true);
    }
}
