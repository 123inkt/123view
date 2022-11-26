<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Revision;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Controller\App\Revision\AttachRevisionController;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Git\Review\CodeReviewService;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Revision\AttachRevisionController
 * @covers ::__construct
 */
class AttachRevisionControllerTest extends AbstractControllerTestCase
{
    private RevisionRepository&MockObject   $revisionRepository;
    private CodeReviewRepository&MockObject $reviewRepository;
    private CodeReviewService&MockObject    $reviewService;
    private ReviewEventService&MockObject   $eventService;
    private TranslatorInterface&MockObject  $translator;

    public function setUp(): void
    {
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->reviewRepository   = $this->createMock(CodeReviewRepository::class);
        $this->reviewService      = $this->createMock(CodeReviewService::class);
        $this->eventService       = $this->createMock(ReviewEventService::class);
        $this->translator         = $this->createMock(TranslatorInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeAttachRevision(): void
    {
        $request    = new Request(request: ['revision' => [123 => 1]]);
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setId(456);
        $review->setRepository($repository);
        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setId(123);

        $this->revisionRepository->expects(self::once())->method('findBy')->with(['id' => [123]])->willReturn([$revision]);

        // expect revision to be attached, saved and dispatched
        $this->reviewService->expects(self::once())->method('addRevisions')->with($review, [$revision], true);
        $this->reviewRepository->expects(self::once())->method('save')->with($review, true);
        $this->eventService->expects(self::once())->method('revisionsAdded')->with($review, [$revision]);

        // expect flash message
        $this->translator->expects(self::once())->method('trans')->with('revisions.added.to.review')->willReturn('message');
        $this->expectAddFlash('success', 'message');

        // expect redirect
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);
    }

    /**
     * Skip attach revision when review repository is not the same as the revision repository
     * @covers ::__invoke
     */
    public function testInvokeSkipRevision(): void
    {
        $request = new Request(request: ['revision' => [123 => 1]]);
        $review  = new CodeReview();
        $review->setId(456);
        $review->setRepository(new Repository());
        $revision = new Revision();
        $revision->setRepository(new Repository());
        $revision->setId(123);

        $this->revisionRepository->expects(self::once())->method('findBy')->with(['id' => [123]])->willReturn([$revision]);

        $this->reviewService->expects(self::never())->method('addRevisions');
        $this->reviewRepository->expects(self::never())->method('save');
        $this->eventService->expects(self::never())->method('revisionsAdded');

        // expect flash message
        $this->translator->expects(self::once())->method('trans')->with('revisions.skipped.to.add.to.review')->willReturn('message');
        $this->expectAddFlash('warning', 'message');

        // expect redirect
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);
    }

    public function getController(): AbstractController
    {
        return new AttachRevisionController(
            $this->revisionRepository,
            $this->reviewRepository,
            $this->reviewService,
            $this->eventService,
            $this->translator
        );
    }
}
