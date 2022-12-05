<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Revision;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Controller\App\Revision\DetachRevisionController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\DetachRevisionsFormType;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Revision\DetachRevisionController
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
        $user = new User();
        $user->setId(456);

        $this->expectGetUser($user);
        $this->expectCreateForm(DetachRevisionsFormType::class, null, ['reviewId' => $review->getId(), 'revisions' => [$revisionA, $revisionB]])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true)
            ->getDataWillReturn(['rev123' => true, 'rev456' => false]);

        $this->revisionRepository->expects(self::once())->method('save')->with($revisionA);
        $this->reviewRepository->expects(self::once())->method('save')->with($review, true);
        $this->eventService->expects(self::once())->method('revisionsDetached')->with($review, [$revisionA], 456);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);

        static::assertNull($revisionA->getReview());
        static::assertFalse($review->getRevisions()->contains($revisionA));
    }

    public function getController(): AbstractController
    {
        return new DetachRevisionController($this->reviewRepository, $this->revisionRepository, $this->eventService);
    }
}
