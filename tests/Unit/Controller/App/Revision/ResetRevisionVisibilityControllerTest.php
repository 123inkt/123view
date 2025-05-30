<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Revision;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Controller\App\Revision\ResetRevisionVisibilityController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionVisibility;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<ResetRevisionVisibilityController>
 */
#[CoversClass(ResetRevisionVisibilityController::class)]
class ResetRevisionVisibilityControllerTest extends AbstractControllerTestCase
{
    private RevisionVisibilityService&MockObject    $visibilityService;
    private RevisionVisibilityRepository&MockObject $visibilityRepository;
    private CodeReviewRevisionService&MockObject    $revisionService;

    protected function setUp(): void
    {
        $this->visibilityService    = $this->createMock(RevisionVisibilityService::class);
        $this->visibilityRepository = $this->createMock(RevisionVisibilityRepository::class);
        $this->revisionService      = $this->createMock(CodeReviewRevisionService::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $review     = new CodeReview();
        $revision   = new Revision();
        $visibility = (new RevisionVisibility())->setVisible(false);
        $user       = new User();

        $this->expectGetUser($user);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->visibilityService->expects($this->once())->method('getRevisionVisibilities')
            ->with($review, [$revision], $user)
            ->willReturn([$visibility]);
        $this->visibilityRepository->expects($this->once())->method('saveAll')->with([$visibility], true);
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($review);
        static::assertTrue($visibility->isVisible());
    }

    public function getController(): AbstractController
    {
        return new ResetRevisionVisibilityController($this->visibilityService, $this->visibilityRepository, $this->revisionService);
    }
}
