<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use Doctrine\ORM\NonUniqueResultException;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeReview\Branch\BranchReviewTargetBranchService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class CodeReviewCreationService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CodeReviewFactory $reviewFactory,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly BranchReviewTargetBranchService $targetBranchService,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function createFromRevision(Revision $revision, ?string $referenceId = null): CodeReview
    {
        $review = $this->reviewFactory->createFromRevision($revision, $referenceId);
        $review->setProjectId($this->reviewRepository->getCreateProjectId((int)$revision->getRepository()->getId()));
        $this->logger?->info('Created new review CR-' . $review->getProjectId());

        return $review;
    }

    /**
     * @throws Throwable
     */
    public function createFromBranch(Repository $repository, string $branchName): CodeReview
    {
        $review = $this->reviewFactory->createFromBranch($repository, $branchName);
        $review->setProjectId($this->reviewRepository->getCreateProjectId((int)$repository->getId()));
        $review->setTargetBranch($this->targetBranchService->getTargetBranch($repository, $branchName));
        $this->logger?->info('Created new branch review CR-' . $review->getProjectId());

        return $review;
    }
}
