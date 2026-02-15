<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\CreateReviewFromRevisionController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Service\CodeReview\CodeReviewCreationService;
use DR\Review\Service\Git\Review\CodeReviewService;
use DR\Review\Service\Webhook\ReviewRevisionEventService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @extends AbstractControllerTestCase<CreateReviewFromRevisionController>
 */
#[CoversClass(CreateReviewFromRevisionController::class)]
class CreateReviewFromRevisionControllerTest extends AbstractControllerTestCase
{
    private CodeReviewCreationService&MockObject  $reviewCreationService;
    private CodeReviewService&MockObject          $reviewService;
    private ReviewRevisionEventService&MockObject $eventService;

    protected function setUp(): void
    {
        $this->reviewCreationService = $this->createMock(CodeReviewCreationService::class);
        $this->reviewService         = $this->createMock(CodeReviewService::class);
        $this->eventService          = $this->createMock(ReviewRevisionEventService::class);
        parent::setUp();
    }

    public function testInvokeOnlyAllowUnattachedReview(): void
    {
        $this->reviewCreationService->expects($this->never())->method('createFromRevision');
        $this->reviewService->expects($this->never())->method('addRevisions');
        $this->eventService->expects($this->never())->method('revisionAddedToReview');
        $review   = (new CodeReview())->setId(123);
        $revision = new Revision();
        $revision->setReview($review);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Revision already attached to a review: ');
        ($this->controller)($revision);
    }

    public function testInvoke(): void
    {
        $review   = new CodeReview();
        $revision = new Revision();
        $user     = new User();
        $user->setId(123);

        $this->expectGetUser($user);
        $this->reviewCreationService->expects($this->once())->method('createFromRevision')->with($revision)->willReturn($review);
        $this->reviewService->expects($this->once())->method('addRevisions')->with($review, [$revision]);
        $this->eventService->expects($this->once())->method('revisionAddedToReview')->with(
            $review,
            $revision,
            true,
            CodeReviewStateType::OPEN,
            CodeReviewerStateType::OPEN,
            123
        );
        $this->expectRedirectToRoute(ReviewController::class, ['review' => $review, 'tab' => 'revisions'])->willReturn('url');

        ($this->controller)($revision);
    }

    public function getController(): AbstractController
    {
        return new CreateReviewFromRevisionController($this->reviewCreationService, $this->reviewService, $this->eventService);
    }
}
