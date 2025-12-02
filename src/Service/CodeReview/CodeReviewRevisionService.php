<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\RevList\CacheableGitRevListService;
use DR\Review\Service\Revision\RevisionSorter;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CodeReviewRevisionService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var array<int, Revision[]> */
    private array $revisions = [];

    public function __construct(
        private readonly CacheableGitRevListService $revListService,
        private readonly RevisionRepository $revisionRepository,
        private readonly RevisionSorter $revisionSorter
    ) {
    }

    /**
     * @return Revision[]
     */
    public function getRevisions(CodeReview $review): array
    {
        if ($review->getType() === CodeReviewType::COMMITS) {
            return $this->revisionSorter->sort($review->getRevisions()->toArray());
        }

        $reviewId = (int)$review->getId();
        if (isset($this->revisions[$reviewId])) {
            return $this->revisions[$reviewId];
        }

        $repository = Assert::notNull($review->getRepository());
        try {
            $hashes = $this->revListService->getCommitsAheadOf($repository, Assert::notNull($review->getReferenceId()), $review->getTargetBranch());
        } catch (RepositoryException|ProcessFailedException|InvalidArgumentException $e) {
            $this->logger?->info('Unable to get revisions for branch review: ' . $review->getId(), ['exception' => $e]);

            return [];
        }

        $revisions = $this->revisionRepository->findBy(['repository' => $repository, 'commitHash' => $hashes], ['createTimestamp' => 'ASC']);

        // reindex array by revision id
        $revisions = Arrays::reindex($revisions, static fn(Revision $revision) => (int)$revision->getId());

        // sort revisions by either sort uuid or create_timestamp
        $revisions = $this->revisionSorter->sort($revisions);

        return $this->revisions[$reviewId] = $revisions;
    }
}
