<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\Review\CodeReviewTypeDecider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReviewTypeDecider::class)]
class CodeReviewTypeDeciderTest extends AbstractTestCase
{
    private CodeReviewTypeDecider $decider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->decider = new CodeReviewTypeDecider();
    }

    public function testDecideCommits(): void
    {
        $review = (new CodeReview())->setType(CodeReviewType::COMMITS);

        static::assertSame(CodeReviewType::COMMITS, $this->decider->decide($review, [], []));
    }

    public function testDecideBranchWithNotAllRevisionsVisible(): void
    {
        $review           = (new CodeReview())->setType(CodeReviewType::BRANCH);
        $revisions        = [new Revision(), new Revision()];
        $visibleRevisions = [new Revision()];

        static::assertSame(CodeReviewType::COMMITS, $this->decider->decide($review, $revisions, $visibleRevisions));
    }

    public function testDecideBranchWithAllRevisionsVisible(): void
    {
        $review           = (new CodeReview())->setType(CodeReviewType::BRANCH);
        $revisions        = [new Revision(), new Revision()];
        $visibleRevisions = [new Revision(), new Revision()];

        static::assertSame(CodeReviewType::BRANCH, $this->decider->decide($review, $revisions, $visibleRevisions));
    }
}
