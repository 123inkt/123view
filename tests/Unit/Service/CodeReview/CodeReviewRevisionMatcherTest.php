<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

use Doctrine\ORM\NonUniqueResultException;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewFactory;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewRevisionMatcher;
use DR\GitCommitNotification\Service\Revision\RevisionPatternMatcher;
use DR\GitCommitNotification\Service\Revision\RevisionTitleNormalizer;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeReview\CodeReviewRevisionMatcher
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
            $this->patternMatcher
        );
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
