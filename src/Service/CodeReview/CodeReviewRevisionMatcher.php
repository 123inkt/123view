<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use Doctrine\ORM\NonUniqueResultException;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Revision\RevisionPatternMatcher;
use DR\Review\Service\Revision\RevisionTitleNormalizer;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class CodeReviewRevisionMatcher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var string[] */
    private array $excludeAuthors;

    public function __construct(
        private readonly RevisionTitleNormalizer $titleNormalizer,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly RevisionPatternMatcher $patternMatcher,
        private readonly CodeReviewCreationService $reviewCreationService,
        readonly string $codeReviewExcludeAuthors
    ) {
        $this->excludeAuthors = $codeReviewExcludeAuthors === '' ? [] : explode(',', $codeReviewExcludeAuthors);
    }

    /**
     * @phpstan-assert Revision $revision
     */
    public function isSupported(?Revision $revision): bool
    {
        if ($revision === null) {
            $this->logger?->info('Matcher: revision not supported: revision is null');

            return false;
        }

        if ($revision->getRepository()->isActive() === false) {
            $this->logger?->info('Matcher: revision repository is inactive: {id}', ['id' => $revision->getId()]);

            return false;
        }

        if ($revision->getCreateTimestamp() < $revision->getRepository()->getCreateTimestamp()) {
            $this->logger?->info('Matcher: revision was created before the repository was added to the project: {id}', ['id' => $revision->getId()]);

            return false;
        }

        if (in_array($revision->getAuthorEmail(), $this->excludeAuthors, true)) {
            $this->logger?->notice('Matcher: revision is excluded by author: {email}', ['email' => $revision->getAuthorEmail()]);

            return false;
        }

        return true;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function match(Revision $revision): ?CodeReview
    {
        // normalize message
        $revisionTitle = $this->titleNormalizer->normalize($revision->getTitle());

        // get review id matcher
        $referenceId = $this->patternMatcher->match($revisionTitle);
        if ($referenceId === null) {
            $this->logger?->info('CodeReviewRevisionMatcher: revision doesn\'t match pattern: ' . $revision->getTitle());

            return null;
        }

        /** @var CodeReview|null $review */
        $review = $this->reviewRepository->findOneByReferenceId(
            (int)Assert::notNull($revision->getRepository())->getId(),
            $referenceId,
            CodeReviewType::COMMITS
        );

        // create new review, and generate project id
        return $review ?? $this->reviewCreationService->createFromRevision($revision, $referenceId);
    }
}
