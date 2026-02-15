<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Revision;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Controller\App\Revision\UpdateRevisionVisibilityController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionVisibility;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\Revision\RevisionVisibilityFormType;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @extends AbstractControllerTestCase<UpdateRevisionVisibilityController>
 */
#[CoversClass(UpdateRevisionVisibilityController::class)]
class UpdateRevisionVisibilityControllerTest extends AbstractControllerTestCase
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

    public function testInvokeBadSubmit(): void
    {
        $user = new User();

        $revision = new Revision();
        $revision->setId(456);

        $visibility = new RevisionVisibility();

        $review = new CodeReview();
        $review->setId(123);

        $request = new Request();

        $this->expectGetUser($user);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->visibilityService->expects($this->once())
            ->method('getRevisionVisibilities')
            ->with($review, [$revision], $user)
            ->willReturn([$visibility]);
        $this->visibilityRepository->expects($this->never())->method('saveAll');
        $this->expectCreateForm(RevisionVisibilityFormType::class, ['visibilities' => [$visibility]], ['reviewId' => 123])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(false);

        $this->expectException(BadRequestHttpException::class);
        ($this->controller)($request, $review);
    }

    public function testInvoke(): void
    {
        $user = new User();

        $revision = new Revision();
        $revision->setId(456);

        $visibility = new RevisionVisibility();

        $review = new CodeReview();
        $review->setId(123);

        $request = new Request();

        $this->expectGetUser($user);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->visibilityService->expects($this->once())
            ->method('getRevisionVisibilities')
            ->with($review, [$revision], $user)
            ->willReturn([$visibility]);
        $this->expectCreateForm(RevisionVisibilityFormType::class, ['visibilities' => [$visibility]], ['reviewId' => 123])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);
        $this->visibilityRepository->expects($this->once())->method('saveAll')->with([$visibility], true);
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $review);
        static::assertFalse($visibility->isVisible());
    }

    public function getController(): AbstractController
    {
        return new UpdateRevisionVisibilityController($this->visibilityService, $this->visibilityRepository, $this->revisionService);
    }
}
