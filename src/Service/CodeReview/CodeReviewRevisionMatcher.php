<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Service\Revision\RevisionPatternMatcher;
use DR\GitCommitNotification\Service\Revision\RevisionTitleNormalizer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class CodeReviewRevisionMatcher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private RevisionTitleNormalizer $titleNormalizer,
        private CodeReviewRepository $reviewRepository,
        private CodeReviewFactory $reviewFactory,
        private RevisionPatternMatcher $patternMatcher
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function match(Revision $revision): ?CodeReview
    {
        // normalize message
        $revisionTitle = $this->titleNormalizer->normalize($revision->getTitle());

        // get review id matcher
        $reviewTitleIdentifier = $this->patternMatcher->match($revisionTitle);
        if ($reviewTitleIdentifier === null) {
            $this->logger?->info('CodeReviewRevisionMatcher: revision doesn\'t match pattern: ' . $revision->getTitle());

            return null;
        }

        /** @var CodeReview|null $review */
        $review = $this->reviewRepository->findOneByTitle($revision->getRepository()->getId(), $reviewTitleIdentifier);

        return $review ?? $this->reviewFactory->createFromRevision($revision);
    }
}
