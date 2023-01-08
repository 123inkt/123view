<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Revision;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Controller\App\Revision\AttachRevisionController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\Review\CodeReviewService;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Revision\AttachRevisionController
 * @covers ::__construct
 */
class AttachRevisionControllerTest extends AbstractControllerTestCase
{
    private RevisionRepository&MockObject  $revisionRepository;
    private CodeReviewService&MockObject   $reviewService;
    private ReviewEventService&MockObject  $eventService;
    private TranslatorInterface&MockObject $translator;

    public function setUp(): void
    {
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
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

        $user = new User();
        $user->setId(456);

        $this->expectGetUser($user);
        $this->revisionRepository->expects(self::once())->method('findBy')->with(['id' => [123]])->willReturn([$revision]);

        // expect revision to be attached, saved and dispatched
        $this->reviewService->expects(self::once())->method('addRevisions')->with($review, [$revision]);
        $this->eventService->expects(self::once())->method('revisionsAdded')->with($review, [$revision], 456);

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
            $this->reviewService,
            $this->eventService,
            $this->translator
        );
    }
}
