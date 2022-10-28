<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewFactory;
use DR\GitCommitNotification\Service\Revision\RevisionTitleNormalizer;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeReview\CodeReviewFactory
 * @covers ::__construct
 */
class CodeReviewFactoryTest extends AbstractTestCase
{
    private RevisionTitleNormalizer&MockObject $titleNormalizer;
    private CodeReviewFactory                  $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->titleNormalizer = $this->createMock(RevisionTitleNormalizer::class);
        $this->factory         = new CodeReviewFactory($this->titleNormalizer);
    }

    /**
     * @covers ::createFromRevision
     */
    public function testCreateFromRevision(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $revision->setTitle('foobar');
        $revision->setRepository($repository);

        $this->titleNormalizer->expects(self::once())->method('normalize')->with('foobar')->willReturn('foobar');

        $review = $this->factory->createFromRevision($revision, 'referenceId');
        static::assertSame('foobar', $review->getTitle());
        static::assertSame($repository, $review->getRepository());
        static::assertSame('referenceId', $review->getReferenceId());
    }
}
