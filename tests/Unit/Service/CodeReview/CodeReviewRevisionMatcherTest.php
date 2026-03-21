<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use Doctrine\ORM\NonUniqueResultException;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeReview\CodeReviewCreationService;
use DR\Review\Service\CodeReview\CodeReviewRevisionMatcher;
use DR\Review\Service\Revision\RevisionPatternMatcher;
use DR\Review\Service\Revision\RevisionTitleNormalizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewRevisionMatcher::class)]
class CodeReviewRevisionMatcherTest extends AbstractTestCase
{
    private RevisionTitleNormalizer&MockObject   $titleNormalizer;
    private CodeReviewRepository&MockObject      $reviewRepository;
    private CodeReviewCreationService&MockObject $reviewCreationService;
    private RevisionPatternMatcher&MockObject    $patternMatcher;
    private CodeReviewRevisionMatcher            $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->titleNormalizer       = $this->createMock(RevisionTitleNormalizer::class);
        $this->reviewRepository      = $this->createMock(CodeReviewRepository::class);
        $this->reviewCreationService = $this->createMock(CodeReviewCreationService::class);
        $this->patternMatcher        = $this->createMock(RevisionPatternMatcher::class);
        $this->matcher               = new CodeReviewRevisionMatcher(
            $this->titleNormalizer,
            $this->reviewRepository,
            $this->patternMatcher,
            $this->reviewCreationService,
            'sherlock@example.com'
        );
    }

    public function testIsSupportedNullIsNot(): void
    {
        $this->titleNormalizer->expects($this->never())->method('normalize');
        $this->reviewRepository->expects($this->never())->method('findOneByReferenceId');
        $this->reviewCreationService->expects($this->never())->method('createFromRevision');
        $this->patternMatcher->expects($this->never())->method('match');
        static::assertFalse($this->matcher->isSupported(null));
    }

    public function testIsSupportedRepositoryTimestampShouldBeGreaterThanRevisionTimestamp(): void
    {
        $this->titleNormalizer->expects($this->never())->method('normalize');
        $this->reviewRepository->expects($this->never())->method('findOneByReferenceId');
        $this->reviewCreationService->expects($this->never())->method('createFromRevision');
        $this->patternMatcher->expects($this->never())->method('match');
        $repository = new Repository();
        $repository->setCreateTimestamp(20000);

        $revision = new Revision();
        $revision->setCreateTimestamp(10000);
        $revision->setRepository($repository);

        static::assertFalse($this->matcher->isSupported($revision));
    }

    public function testIsSupportedShouldBeActive(): void
    {
        $this->titleNormalizer->expects($this->never())->method('normalize');
        $this->reviewRepository->expects($this->never())->method('findOneByReferenceId');
        $this->reviewCreationService->expects($this->never())->method('createFromRevision');
        $this->patternMatcher->expects($this->never())->method('match');
        $repository = new Repository();
        $repository->setActive(false);
        $revision   = new Revision();
        $revision->setRepository($repository);

        static::assertFalse($this->matcher->isSupported($revision));
    }

    public function testIsSupportedAuthorShouldBeExcluded(): void
    {
        $this->titleNormalizer->expects($this->never())->method('normalize');
        $this->reviewRepository->expects($this->never())->method('findOneByReferenceId');
        $this->reviewCreationService->expects($this->never())->method('createFromRevision');
        $this->patternMatcher->expects($this->never())->method('match');
        $repository = new Repository();
        $repository->setCreateTimestamp(10000);

        $revision = new Revision();
        $revision->setCreateTimestamp(20000);
        $revision->setRepository($repository);
        $revision->setAuthorEmail('sherlock@example.com');

        static::assertFalse($this->matcher->isSupported($revision));
    }

    public function testIsSupported(): void
    {
        $this->titleNormalizer->expects($this->never())->method('normalize');
        $this->reviewRepository->expects($this->never())->method('findOneByReferenceId');
        $this->reviewCreationService->expects($this->never())->method('createFromRevision');
        $this->patternMatcher->expects($this->never())->method('match');
        $repository = new Repository();
        $repository->setCreateTimestamp(10000);

        $revision = new Revision();
        $revision->setCreateTimestamp(20000);
        $revision->setRepository($repository);
        $revision->setAuthorEmail('holmes@example.com');

        static::assertTrue($this->matcher->isSupported($revision));
    }

    /**
     * @throws NonUniqueResultException
     */
    public function testMatchRevisionTitleHasNoMatch(): void
    {
        $revision = new Revision();
        $revision->setTitle('foobar');

        $this->titleNormalizer->expects($this->once())->method('normalize')->willReturnArgument(0);
        $this->patternMatcher->expects($this->once())->method('match')->with('foobar')->willReturn(null);
        $this->reviewRepository->expects($this->never())->method('findOneByReferenceId');
        $this->reviewCreationService->expects($this->never())->method('createFromRevision');

        static::assertNull($this->matcher->match($revision));
    }

    /**
     * @throws NonUniqueResultException
     */
    public function testMatchRevisionHasExistingReview(): void
    {
        $revision = new Revision();
        $revision->setTitle('F#123 US#456 T#890 Task');
        $revision->setRepository((new Repository())->setId(5));

        $review = new CodeReview();

        $this->titleNormalizer->expects($this->once())->method('normalize')->willReturnArgument(0);
        $this->patternMatcher->expects($this->once())->method('match')->with('F#123 US#456 T#890 Task')->willReturn('T#890');
        $this->reviewRepository->expects($this->once())
            ->method('findOneByReferenceId')
            ->with(5, 'T#890', CodeReviewType::COMMITS)
            ->willReturn($review);
        $this->reviewCreationService->expects($this->never())->method('createFromRevision');

        static::assertSame($review, $this->matcher->match($revision));
    }

    /**
     * @throws NonUniqueResultException
     */
    public function testMatchRevisionHasNoReview(): void
    {
        $revision = new Revision();
        $revision->setTitle('F#123 US#456 T#890 Task');
        $revision->setRepository((new Repository())->setId(5));

        $review = new CodeReview();

        $this->titleNormalizer->expects($this->once())->method('normalize')->willReturnArgument(0);
        $this->patternMatcher->expects($this->once())->method('match')->with('F#123 US#456 T#890 Task')->willReturn('T#890');
        $this->reviewRepository->expects($this->once())->method('findOneByReferenceId')->with(5, 'T#890', CodeReviewType::COMMITS)->willReturn(null);
        $this->reviewCreationService->expects($this->once())->method('createFromRevision')->with($revision)->willReturn($review);

        static::assertSame($review, $this->matcher->match($revision));
    }
}
