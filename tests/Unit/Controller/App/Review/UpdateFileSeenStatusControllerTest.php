<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\UpdateFileSeenStatusController;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Request\Review\FileSeenStatusRequest;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Service\Git\Review\ReviewSessionService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends AbstractControllerTestCase<UpdateFileSeenStatusController>
 */
#[CoversClass(UpdateFileSeenStatusController::class)]
class UpdateFileSeenStatusControllerTest extends AbstractControllerTestCase
{
    private FileSeenStatusService&MockObject      $statusService;
    private ReviewDiffServiceInterface&MockObject $diffService;
    private ReviewSessionService&MockObject       $sessionService;
    private CodeReviewRevisionService&MockObject  $revisionService;

    public function setUp(): void
    {
        $this->statusService   = $this->createMock(FileSeenStatusService::class);
        $this->diffService     = $this->createMock(ReviewDiffServiceInterface::class);
        $this->sessionService  = $this->createMock(ReviewSessionService::class);
        $this->revisionService = $this->createMock(CodeReviewRevisionService::class);
        parent::setUp();
    }

    public function testInvokeMarkAsSeen(): void
    {
        $request = $this->createMock(FileSeenStatusRequest::class);
        $request->method('getFilePath')->willReturn('filepath');
        $request->method('getSeenStatus')->willReturn(true);

        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        $diffFileA                = new DiffFile();
        $diffFileA->filePathAfter = 'filepath';
        $diffFileB                = new DiffFile();

        $user = new User();
        $this->expectGetUser($user);

        $this->revisionService->expects(self::once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects(self::once())
            ->method('getDiffForRevisions')
            ->with($repository, [$revision])
            ->willReturn([$diffFileA, $diffFileB]);
        $this->sessionService->expects(self::once())->method('getDiffComparePolicyForUser')->willReturn(DiffComparePolicy::ALL);
        $this->statusService->expects(self::once())->method('markAsSeen')->with($review, $user, $diffFileA);

        /** @var Response $response */
        $response = ($this->controller)($request, $review);
        static::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
    }

    public function testInvokeMarkAsUnseen(): void
    {
        $request = $this->createMock(FileSeenStatusRequest::class);
        $request->method('getFilePath')->willReturn('filepath');
        $request->method('getSeenStatus')->willReturn(false);

        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);

        $diffFileA                = new DiffFile();
        $diffFileA->filePathAfter = 'filepath';
        $diffFileB                = new DiffFile();

        $user = new User();
        $this->expectGetUser($user);

        $this->revisionService->expects(self::once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects(self::once())
            ->method('getDiffForRevisions')
            ->with($repository, [$revision])
            ->willReturn([$diffFileA, $diffFileB]);
        $this->sessionService->expects(self::once())->method('getDiffComparePolicyForUser')->willReturn(DiffComparePolicy::ALL);
        $this->statusService->expects(self::once())->method('markAsUnseen')->with($review, $user, $diffFileA);

        /** @var Response $response */
        $response = ($this->controller)($request, $review);
        static::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
    }

    public function testInvokeFilepathDoesntMatchAFile(): void
    {
        $request = $this->createMock(FileSeenStatusRequest::class);
        $request->method('getFilePath')->willReturn('filepath');
        $request->method('getSeenStatus')->willReturn(false);

        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        $diffFile = new DiffFile();

        $this->revisionService->expects(self::once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects(self::once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$diffFile]);
        $this->sessionService->expects(self::once())->method('getDiffComparePolicyForUser')->willReturn(DiffComparePolicy::ALL);

        /** @var Response $response */
        $response = ($this->controller)($request, $review);
        static::assertSame(Response::HTTP_NOT_MODIFIED, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new UpdateFileSeenStatusController($this->statusService, $this->diffService, $this->sessionService, $this->revisionService);
    }
}
