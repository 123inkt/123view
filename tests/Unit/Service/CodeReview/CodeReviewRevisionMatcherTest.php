<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use Doctrine\ORM\NonUniqueResultException;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Revision;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeReview\CodeReviewFactory;
use DR\Review\Service\CodeReview\CodeReviewRevisionMatcher;
use DR\Review\Service\Revision\RevisionPatternMatcher;
use DR\Review\Service\Revision\RevisionTitleNormalizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\CodeReview\CodeReviewRevisionMatcher
 * @covers ::__construct
 */
class CodeReviewRevisionMatcherTest extends AbstractTestCase
{
    private RevisionTitleNormalizer&MockObject $titleNormalizer;
    private CodeReviewRepository&MockObject    $reviewRepository;
    private CodeReviewFactory&MockObject       $reviewFactory;
    private RevisionPatternMatcher&MockObject  $patternMatcher;
    private CodeReviewRevisionMatcher          $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->titleNormalizer  = $this->createMock(RevisionTitleNormalizer::class);
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->reviewFactory    = $this->createMock(CodeReviewFactory::class);
        $this->patternMatcher   = $this->createMock(RevisionPatternMatcher::class);
        $this->matcher          = new CodeReviewRevisionMatcher(
            $this->titleNormalizer,
            $this->reviewRepository,
            $this->reviewFactory,
            $this->patternMatcher,
            'sherlock@example.com'
        );
    }

    /**
     * @covers ::isSupported
     */
    public function testIsSupportedNullIsNot(): void
    {
        static::assertFalse($this->matcher->isSupported(null));
    }

    /**
     * @covers ::isSupported
     */
    public function testIsSupportedRepositoryTimestampShouldBeGreaterThanRevisionTimestamp(): void
    {
        $repository = new Repository();
        $repository->setCreateTimestamp(20000);

        $revision = new Revision();
        $revision->setCreateTimestamp(10000);
        $revision->setRepository($repository);

        static::assertFalse($this->matcher->isSupported($revision));
    }

    /**
     * @covers ::isSupported
     */
    public function testIsSupportedAuthorShouldBeExcluded(): void
    {
        $repository = new Repository();
        $repository->setCreateTimestamp(10000);

        $revision = new Revision();
        $revision->setCreateTimestamp(20000);
        $revision->setRepository($repository);
        $revision->setAuthorEmail('sherlock@example.com');

        static::assertFalse($this->matcher->isSupported($revision));
    }

    /**
     * @covers ::isSupported
     */
    public function testIsSupported(): void
    {
        $repository = new Repository();
        $repository->setCreateTimestamp(10000);

        $revision = new Revision();
        $revision->setCreateTimestamp(20000);
        $revision->setRepository($repository);
        $revision->setAuthorEmail('holmes@example.com');

        static::assertTrue($this->matcher->isSupported($revision));
    }

    /**
     * @covers ::match
     * @throws NonUniqueResultException
     */
    public function testMatchRevisionTitleHasNoMatch(): void
    {
        $revision = new Revision();
        $revision->setTitle('foobar');

        $this->titleNormalizer->expects(self::once())->method('normalize')->willReturnArgument(0);
        $this->patternMatcher->expects(self::once())->method('match')->with('foobar')->willReturn(null);

        static::assertNull($this->matcher->match($revision));
    }

    /**
     * @covers ::match
     * @throws NonUniqueResultException
     */
    public function testMatchRevisionHasExistingReview(): void
    {
        $revision = new Revision();
        $revision->setTitle('F#123 US#456 T#890 Task');
        $revision->setRepository((new Repository())->setId(5));

        $review = new CodeReview();

        $this->titleNormalizer->expects(self::once())->method('normalize')->willReturnArgument(0);
        $this->patternMatcher->expects(self::once())->method('match')->with('F#123 US#456 T#890 Task')->willReturn('T#890');
        $this->reviewRepository->expects(self::once())->method('findOneByReferenceId')->with(5, 'T#890')->willReturn($review);
        $this->reviewFactory->expects(self::never())->method('createFromRevision');

        static::assertSame($review, $this->matcher->match($revision));
    }

    /**
     * @covers ::match
     * @throws NonUniqueResultException
     */
    public function testMatchRevisionHasNoReview(): void
    {
        $revision = new Revision();
        $revision->setTitle('F#123 US#456 T#890 Task');
        $revision->setRepository((new Repository())->setId(5));

        $review = new CodeReview();

        $this->titleNormalizer->expects(self::once())->method('normalize')->willReturnArgument(0);
        $this->patternMatcher->expects(self::once())->method('match')->with('F#123 US#456 T#890 Task')->willReturn('T#890');
        $this->reviewRepository->expects(self::once())->method('findOneByReferenceId')->with(5, 'T#890')->willReturn(null);
        $this->reviewFactory->expects(self::once())->method('createFromRevision')->with($revision)->willReturn($review);

        static::assertSame($review, $this->matcher->match($revision));
    }
}
