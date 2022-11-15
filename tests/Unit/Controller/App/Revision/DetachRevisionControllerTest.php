<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Revision;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Controller\App\Revision\DetachRevisionController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Form\Review\DetachRevisionsFormType;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Revision\DetachRevisionController
 * @covers ::__construct
 */
class DetachRevisionControllerTest extends AbstractControllerTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private RevisionRepository&MockObject   $revisionRepository;
    private ReviewEventService&MockObject   $eventService;

    public function setUp(): void
    {
        $this->reviewRepository   = $this->createMock(CodeReviewRepository::class);
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->eventService       = $this->createMock(ReviewEventService::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeBadFormSubmit(): void
    {
        $request = new Request();

        $revision = new Revision();
        $revision->setId(123);
        $review = new CodeReview();
        $review->setId(456);
        $review->getRevisions()->add($revision);

        $this->expectCreateForm(DetachRevisionsFormType::class, null, ['reviewId' => $review->getId(), 'revisions' => [$revision]])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Submitted invalid form');
        ($this->controller)($request, $review);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeValidFormSubmit(): void
    {
        $request = new Request();

        $revisionA = new Revision();
        $revisionA->setId(123);
        $revisionB = new Revision();
        $revisionB->setId(456);
        $review = new CodeReview();
        $review->setId(456);
        $review->getRevisions()->add($revisionA);
        $review->getRevisions()->add($revisionB);

        $this->expectCreateForm(DetachRevisionsFormType::class, null, ['reviewId' => $review->getId(), 'revisions' => [$revisionA, $revisionB]])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true)
            ->getDataWillReturn(['rev123' => true, 'rev456' => false]);

        $this->revisionRepository->expects(self::once())->method('save')->with($revisionA);
        $this->reviewRepository->expects(self::once())->method('save')->with($review, true);
        $this->eventService->expects(self::once())->method('revisionsDetached')->with($review, [$revisionA]);

        $this->expectRefererRedirect(ReviewController::class, ['id' => 456]);

        ($this->controller)($request, $review);

        static::assertNull($revisionA->getReview());
        static::assertFalse($review->getRevisions()->contains($revisionA));
    }

    public function getController(): AbstractController
    {
        return new DetachRevisionController($this->reviewRepository, $this->revisionRepository, $this->eventService);
    }
}
