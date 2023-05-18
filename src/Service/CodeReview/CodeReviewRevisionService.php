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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class CodeReviewRevisionService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var array<int, Revision[]> */
    private array $revisions = [];

    public function __construct(private readonly GitRevListService $revListService, private readonly RevisionRepository $revisionRepository)
    {
    }

    /**
     * @return Revision[]
     */
    public function getRevisions(CodeReview $review): array
    {
        if ($review->getType() === CodeReviewType::COMMITS) {
            return $review->getRevisions()->toArray();
        }

        $reviewId = (int)$review->getId();
        if (isset($this->revisions[$reviewId])) {
            return $this->revisions[$reviewId];
        }

        $repository = Assert::notNull($review->getRepository());
        try {
            $hashes = $this->revListService->getCommitsAheadOfMaster($repository, Assert::notNull($review->getReferenceId()));
        } catch (RepositoryException $e) {
            $this->logger?->info('Unable to get revisions for branch review: ' . $review->getId(), ['exception' => $e]);

            return [];
        }

        $revisions = $this->revisionRepository->findBy(['repository' => $repository, 'commitHash' => $hashes], ['createTimestamp' => 'ASC']);

        return $this->revisions[$reviewId] = $revisions;
    }
}
