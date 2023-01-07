<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionVisibility;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;

class RevisionVisibilityProvider
{
    public function __construct(private readonly RevisionVisibilityRepository $visibilityRepository)
    {
    }

    /**
     * @param Revision[] $revisions
     *
     * @return RevisionVisibility[]
     */
    public function getRevisionVisibilities(CodeReview $review, array $revisions, User $user): array
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
                ->setUser($user)
                ->setRevision($revision)
                ->setVisible(true);
        }

        return $result;
    }
}
