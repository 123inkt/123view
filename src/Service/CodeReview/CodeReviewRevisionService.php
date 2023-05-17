<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\RevList\GitRevListService;
use DR\Review\Utility\Assert;

class CodeReviewRevisionService
{
    public function __construct(private readonly GitRevListService $revListService, private readonly RevisionRepository $revisionRepository)
    {
    }

    /**
     * @return Revision[]
     * @throws RepositoryException
     */
    public function getRevisions(CodeReview $review): array
    {
        if ($review->getType() === CodeReviewType::COMMITS) {
            return $review->getRevisions()->toArray();
        }

        $repository = Assert::notNull($review->getRepository());
        $hashes     = $this->revListService->getCommitsAheadOfMaster($repository, Assert::notNull($review->getReferenceId()));

        return $this->revisionRepository->findBy(['repository' => $repository, 'commitHash' => $hashes], ['createTimestamp' => 'ASC']);
    }
}
