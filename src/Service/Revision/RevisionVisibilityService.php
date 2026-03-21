<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

use Doctrine\Common\Collections\Collection;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionVisibility;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;
use DR\Review\Service\User\UserEntityProvider;

readonly class RevisionVisibilityService
{
    public function __construct(private UserEntityProvider $userProvider, private RevisionVisibilityRepository $visibilityRepository)
    {
    }

    /**
     * @param Revision[] $revisions
     *
     * @return Revision[]
     */
    public function getVisibleRevisions(CodeReview $review, array $revisions): array
    {
        $visibilities = $this->visibilityRepository->findBy(['review' => $review->getId(), 'user' => (int)$this->userProvider->getUser()?->getId()]);
        if (count($visibilities) === 0) {
            return $revisions;
        }

        $result = [];
        foreach ($revisions as $revision) {
            foreach ($visibilities as $visibility) {
                if ($revision->getId() !== $visibility->getRevision()->getId()) {
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
                if ($revision->getId() === $visibility->getRevision()->getId()) {
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

    /**
     * @param array<Revision>|Collection<int, Revision> $revisions
     */
    public function setRevisionVisibility(CodeReview $review, array|Collection $revisions, User $user, bool $visible): void
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
