<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\UpdateFileSeenStatusController;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Request\Review\FileSeenStatusRequest;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\UpdateFileSeenStatusController
 * @covers ::__construct
 */
class UpdateFileSeenStatusControllerTest extends AbstractControllerTestCase
{
    private FileSeenStatusService&MockObject      $statusService;
    private ReviewDiffServiceInterface&MockObject $diffService;

    public function setUp(): void
    {
        $this->statusService = $this->createMock(FileSeenStatusService::class);
        $this->diffService   = $this->createMock(ReviewDiffServiceInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
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

        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$diffFileA, $diffFileB]);
        $this->statusService->expects(self::once())->method('markAsSeen')->with($review, $user, $diffFileA);

        /** @var Response $response */
        $response = ($this->controller)($request, $review);
        static::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeMarkAsUnseen(): void
    {
        $request = $this->createMock(FileSeenStatusRequest::class);
        $request->method('getFilePath')->willReturn('filepath');
        $request->method('getSeenStatus')->willReturn(false);

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

        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$diffFileA, $diffFileB]);
        $this->statusService->expects(self::once())->method('markAsUnseen')->with($review, $user, $diffFileA);

        /** @var Response $response */
        $response = ($this->controller)($request, $review);
        static::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
    }

    /**
     * @covers ::__invoke
     */
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

        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$diffFile]);

        /** @var Response $response */
        $response = ($this->controller)($request, $review);
        static::assertSame(Response::HTTP_NOT_MODIFIED, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new UpdateFileSeenStatusController($this->statusService, $this->diffService);
    }
}
