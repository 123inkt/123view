<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Api\Gitlab\ReviewApprovalValidatorService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewApprovalValidatorService::class)]
class ReviewApprovalValidatorServiceTest extends AbstractTestCase
{
    private ReviewApprovalValidatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReviewApprovalValidatorService('/^PR-[0-9]+/');
    }

    public function testValidateShouldReturnFalseWhenReviewIsNull(): void
    {
        static::assertFalse($this->service->validate(null, new CodeReviewer(), 123));
    }

    public function testValidateShouldReturnFalseWhenReviewerIsNull(): void
    {
        static::assertFalse($this->service->validate(new CodeReview(), null, 123));
    }

    public function testValidateShouldReturnFalseWhenProjectIdIsNull(): void
    {
        static::assertFalse($this->service->validate(new CodeReview(), new CodeReviewer(), null));
    }

    public function testValidateShouldReturnFalseWhenGitApprovalSyncIsDisabled(): void
    {
        $repository = new Repository();
        $repository->setGitApprovalSync(false);
        $review = new CodeReview();
        $review->setRepository($repository);

        static::assertFalse($this->service->validate($review, new CodeReviewer(), 123));
    }

    public function testValidateShouldReturnFalseWhenRemoteRefIsNull(): void
    {
        $repository = new Repository();
        $repository->setGitApprovalSync(true);
        $review = new CodeReview();
        $review->setRepository($repository);

        static::assertFalse($this->service->validate($review, new CodeReviewer(), 123));
    }

    public function testValidateShouldReturnFalseWhenRemoteRefDoesNotMatchPattern(): void
    {
        $revision = (new Revision())->setFirstBranch('feature-branch');
        $repository = new Repository();
        $repository->setGitApprovalSync(true);
        $review = new CodeReview();
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        static::assertFalse($this->service->validate($review, new CodeReviewer(), 123));
    }

    public function testValidateShouldReturnTrueWhenAllConditionsAreMet(): void
    {
        $revision = (new Revision())->setFirstBranch('PR-12345');
        $repository = new Repository();
        $repository->setGitApprovalSync(true);
        $review = new CodeReview();
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        static::assertTrue($this->service->validate($review, new CodeReviewer(), 123));
    }
}
