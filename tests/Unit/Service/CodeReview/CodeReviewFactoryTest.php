<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\CodeReview\CodeReviewFactory;
use DR\Review\Service\Revision\RevisionTitleNormalizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewFactory::class)]
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

    public function testCreateFromRevision(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $revision->setTitle('foobar');
        $revision->setDescription('description');
        $revision->setRepository($repository);

        $this->titleNormalizer->expects($this->once())->method('normalize')->with('foobar')->willReturn('foobar');

        $review = $this->factory->createFromRevision($revision, 'referenceId');
        static::assertSame('foobar', $review->getTitle());
        static::assertSame(CodeReviewType::COMMITS, $review->getType());
        static::assertSame('description', $review->getDescription());
        static::assertSame($repository, $review->getRepository());
        static::assertSame('referenceId', $review->getReferenceId());
    }

    public function testCreateFromBranch(): void
    {
        $this->titleNormalizer->expects($this->never())->method('normalize');
        $repository = new Repository();

        $review = $this->factory->createFromBranch($repository, 'origin/branch_name');
        static::assertSame('branch name', $review->getTitle());
        static::assertSame(CodeReviewType::BRANCH, $review->getType());
        static::assertSame('', $review->getDescription());
        static::assertSame($repository, $review->getRepository());
        static::assertSame('origin/branch_name', $review->getReferenceId());
    }
}
